# Advanced usage

Some of the more advanced stuff you can do with Z is related to how you
incorporate other useful tools. Here are some suggestions of how to solve
common problems.

## User interaction
If you use Z as a tool for yourself on the command line, you can do all kinds
of handy user interaction, such as choosing an option from a list, asking for text input, etc

## Editing config files with Z
If you install [goggle](https://github.com/zicht/goggle), you have an extremely
useful tool for interactively configuring your application. Goggle is a tool
which is able to edit specific values within a config file at a certain path.

For example, you can write:

```
plugins: ['interact']

globals:
    goggle: 
        get: "vendor/bin/goggle get -i %s %s"
        set: "vendor/bin/goggle set -e %s %s"

    settings:
        - description: "Database host"
          setting: ["app/config/parameters.yml", "parameters", "database_host"]
        - description: "Database name"
          setting: ["app/config/parameters.yml", "parameters", "database_name"]
        - description: "The `coolness` JSON setting"
          setting: ["src/js/settings.json", "settings", "coolness"]
        - description: "The `debug` JSON setting"
          setting: ["src/js/settings.json", "settings", "debug"]

tasks:
    configure: @(for setting in keys globals.settings) $(z.cmd) configure-setting $(setting)

    configure-setting:
        args:
            setting_key: ?
        set:
            setting: globals.settings[setting_key]
            current_value: sh(sprintf(globals.goggle.get, escape setting.path[0], escape slice(setting.path, 1)))
            new_value: ask(setting.description, current_value)
        do: $(sprintf(globals.goggle.set, escape setting.path[0], escape slice(setting.path, 1), escape new_value))
```

The result is a tool with which you can dynamically configure a lot of
different things in an interactive and user friendly way.

## Parallel execution
Z itself does not run parallel jobs. Following the philosophy that anything you
want to do, can already be done in your shell, this also goes for running
parallel jobs. Here are two options you can use.

### Using `xargs`
You can easily run jobs in parallel with `xargs`. The most practical way to set
this up is defining two branches in the `do` of the task, where one is the one
that renders the shell line that executes the job in parallel, and the other
executes the job not parallel. 

Another option is to use the `parallel` flag to choose an appropriate value for
the `xargs`' `-P` value (as shown as a third task line in the example below).


Introduce a flag for `parallel` and you're done:

```
globals:
    list_of_heavy_stuff: ['a', 'b', 'c']

tasks:
    heavy-stuff:
        flags:
            parallel: false
        do:
            - @(if  parallel) echo $(escape globals.list_of_heavy_stuff) | xargs -P 10 -n 1 $(z.cmd) the-task
            - @(if !parallel) @(for the_stuff in globals.list_of_heavy_stuff) $(z.cmd) the-task $(the_stuff)

        # or:
            - echo $(escape globals.list_of_heavy_stuff) | xargs -P $(parallel ? 10 : 1) -n 1 $(z.cmd) the-task

    the-task:
        args:
            the_stuff: ?
        do: sleep 1 && echo "$(the_stuff) is done"
```

Running this:

```
$ time z heavy-stuff 
a is done
b is done
c is done

real    0m3.462s
user    0m0.324s
sys 0m0.120s

$ time z heavy-stuff --parallel
a is done
c is done
b is done

real    0m1.267s
user    0m0.444s
sys 0m0.100s
```

You can also use this if you have multiple "watchers" that you use for
generating front-end assets:

```
tasks:
    compile.js:
        flags:
            watch: false
        do: js_compiler $(watch ? "--watch") src/js

    compile.css:
        flags:
            watch: false
        do: css_compiler $(watch ? "--watch") src/css

    watch: $(escape keys(tasks.compile)) | xargs -I '{}' $(z.cmd) --watch compile:'{}' -P 2
```
The `watch` task now would start two parallel jobs for both watchers.

See the sections on "flags" and "annotations" in the reference for more
information on how to utilize constructs like this.

The common advice that it is a good practice to do `number_of_cpus + 1`, though
in my personal opinion it depends on the contents of the task. If the task is
depending on outside resources (such as github, for example) it's safe to
introduce a higher value for the `-P` flag, since most of the time consumed in
spent in waiting for network resources, which is a `sleep` state.

See `zicht/satis` for an example of how this is implemented to mirror a lot of
repositories in parallel.

### Using `make`
You can also incorporate `make` for using parallel execution. Make has it's own
dependency resolution implementation which can find out how jobs can be
executed in parallel. You can use the `-j` flag to identify how much jobs you
want to run in parallel.

This is particularly useful if you have a build dependency in a classical way.

For example, your Makefile could look like this:

```
.PHONY: app

app: web/main.js vendor/autoload.php

vendor/autoload.php: composer install
node_modules/.bin/tsc: npm install

web/main.js: src/js/main.tsx tsconfig.json node_modules/.bin/tsc
    node_modules/.bin/tsc ./tsconfig.json
```

Running `make -j2 app` would lead `make` to the conclusing that the `composer
install` and the `npm install` can be run in parallel, since the have no common
dependency.

## Using the `tasks` variable

### Creating dynamic sub tasks

A trick to have a base task with several subtasks can be accomplished using a pattern where you'd approach the `tasks` variable. The structure of this variable follows the naming of the tasks, so a dot `.` in the task name would result in a "dictionary" (associative array) following that structure. Combining that with the `keys` function grants you the opportunity to work with these tasks.

If you evaluate a task, it is run. So that gives you the possibility to dynamically construct a set of tasks which are evaluated by a common "main" task:

```
tasks:
    foo: @(for task in keys tasks._foo) $(tasks._foo[task])

    _foo.sub-1: echo "This is sub1"
    _foo.sub-2: echo "This is sub2"
    _foo.sub-3: echo "This is sub3"
```

This is an approach where plugins or imports could provide ways for you to hook into another task.

### Combining with `yield`
Sometimes it's useful to construct a task that yields a piece of code, and does not need to be executed again when it's generated.

More practically, you could, for example, use that to configure a task which downloads a library if it does not yet exist:

```
tasks:
    composer: 
        set:
            _composer_file: "./composer.phar"
        unless: is_file(_composer_file)
        do: wget https://download-composer-from-somewhere/composer.phar
        yield: _composer_file

    install:
        - $(tasks.composer) install
```
