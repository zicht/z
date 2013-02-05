## Writing a z.yml file ##

### Hello world ###
Of course, as we are all programmers or at least we know some, we know that the first thing to do is say hello to the
world. We can accomplish this by defining a yml file and defining a task "say_hello", which has 'echo hello world' as
its body:

```
tasks:
    say_hello:
        do:
            - echo "Hello world!"
```

Save this file as "z.yml" and run z help. Z will now display "say_hello" as a command. Running this command will run
the shell script snippet:

```shell
$ z say_hello
Hello world!
```

Congratulations, you have just implemented your first task in Z!

For convenience, you can abbreviate an array of commands a string, and even a single string command as the definition
of the task. So the following three examples are equivalent:

```
tasks:
    say_hello:
        do:
            - echo "Hello world!"

    say_hello_short:
        do: echo "Hello world!"

    say_hello_shorter: echo "Hello world!"
```

For clarity's sake, we stick to the first version in our examples, but you will find that writing the 'do' section in
the first and second form will come in handy as your tasks become more and more atomic.

### Task input ###
If you add a 'set' section to the task, you will define variables or task input:

```
tasks:
    say:
        set:
            what: '"Hello"'
        do:
            - echo "$(what) world!"
```

> *Note that the notation for the string "Hello" is a bit awkward, because YML would interpret the double quotes as
a regular string, and Z would never know that you put quotes there. Fortunately, this use case isn't that common.*

This is not very helpful, because we would still have the hard coded "Hello" value in our code, even though executing would do the
same as our first example:

```shell
z say
Hello world
```

It would be cool if we could pass in the variable to the command, right? Ok, let's do that:

```
tasks:
    say:
        set:
            what: ? "Hello"
        do:
            - echo "$(what) world!"
```

> *Note that you can skip the single quotes now, because the question mark is part of the value. Fortunately, this use
case is much more common.*

Now, you have declared input for your task, which is available through the command line:

```shell
$ z say
Hello world!

$ z say Boogiewoogie
Boogiewoogie world!
```

By simply adding a question mark to the input declaration, you have made the input variable. If you omit the default value, the input will become required:

```
tasks:
    say:
        set:
            what: ? "Hello"
        do:
            - echo "$(what) world!"
```

```shell
$ z say

[RuntimeException]
Not enough arguments.

say [--explain] [-f|--force] what

$ z say Hello
Hello world!
```

You can even add interactivity to your script, by including the ask() function to the declaration:

```
plugins: ['core']

tasks:
    say:
        set:
            what: ? ask("You did not specify what! Please tell me!", "Hello anyway")
        do:
            - echo "$(what) world!"
```

```shell
$ z say
You did not specify what! Please tell me!? [Hello anyway]
Hello anyway world!

$ z say Modern
Modern world!
```

As you can see, you need the 'core' plugin for this example. More about plugins later.

# Chaining or triggering tasks #
By prepending variables with an underscore, you are declaring them private. This means that the command line will not
pass input to the task, but other tasks can define the input for it:

```
tasks:
    say_hello:
        set:
            _what: "Hello"
        do:
            - @say

    say:
        set:
            _what: ?
        do:
            - echo "$(_what) world!"
```

As you can see, referring another task is done by prepending the task line with an at-sign. Internally, this is
represented as the variable $tasks.say.

In essence, you are declaring that the 'say' task cannot be executed without another task providing a value as it's
input. This would mean that the task itself could be considered private, as it can not be executed from the command
line. It will always throw an error about the missing '_what' parameter:

```shell
$ z say

  [RuntimeException]
  required variable _what is not defined

say_hello [--explain] [-f|--force]

$ z say_hello
[                  say] Hello world!
```

Of course, the say_hello task can by itself publish the variable:

```
tasks:
    say_hello:
        set:
            what: ? "Hello"
            _what: what
        do:
            - @say

    say:
        set:
            _what: ?
        do:
            - echo "$(_what) world!"
```

```shell
$ z say

  [RuntimeException]
  required variable _what is not defined

say [--explain] [-f|--force]

$ z say_hello
[                  say] Hello world!

$ z say_hello boo
[                  say] boo world!
```

Notice that Z by default shows where the output comes from if the task depth is deeper than 1. This means that the name
of any task that is triggered or depended upon is displayed before the output the task renders.

# Explaining commands #
Now is a good time to tell a little bit more about what Z actually does. By convention, and by ideology, Z does nothing
but find out what tasks need to be executed and provide these tasks to the shell. That means that anything that is
actually **done**, is done by the shell. When you start to implement your own tasks or even plugins, this is critical
to the way Z functions. Because this way, Z can generate scripts for any task that is executed, by simply adding a
parameter:

```shell
    $ z say_hello --explain
    ( echo "Hello world!" );

    $ z say_hello "Foo bar baz!"
    ( echo "Foo bar baz world!" );
```

This is very helpful in debugging your tasks and explains a task better than any other way of describing, documenting,
etcetera. Of course, you could add "dry run" version of any task you might want to execute, but it's much harder
actually dry-running something, than simply showing what "would have been done" exactly. Of course, the script output
might be cryptic at times, but then again, if what the task would be doing was easy, you probably wouldn't have the need
to explain it in the first place.

The reason all lines are displayed between parentheses, is that each of the line is effectively executed in a separate
process and thus in a subshell. However, if you define the task body as a string separated by new lines, the task line
is executed in one script:

```
tasks:
    long_line:
        do: |
            echo            \
                This        \
                is          \
                spanned     \
            ;               \
            echo            \
                Over        \
                multiple    \
                lines       \
            ;
```

```shell
$ z --explain long_line
( echo            \
    This        \
    is          \
    spanned     \
;               \
echo            \
    Over        \
    multiple    \
    lines       \
; );
```


# Adding help #
You can add help to your commands by adding a "help" section. This section is read by the command line runner and passed
as an info line for the command and a help for the help display of the command:

```
tasks:
    do_nothing:
        help: |
            Do nothing.

            This task does nothing. No really. Nothing.
            Well, alright, it displays help if you ask for it.

        do: []
```

# Conditionals #
A task may be conditional, i.e., if there is some check done and that check says 'true', you might want to skip the
task. A simple example is checking if something is up to date, before overwriting it. These conditionals can best be
used in conjunction with functions from the core plugin, such as 'mtime', but for the sake of the example, we will just
include a simple expression.

These conditionals are identified by the 'unless' section:

```
tasks:
    say:
        set:
            times: ? 2
        # avoid over-exaggerated hello-saying
        unless: times > 5
        do:
            - for i in $$(seq 1 $(times)); do echo "Hello!"; done;
```

```shell
$ z say 4
Hello!
Hello!
Hello!
Hello!

$ z say 6
say skipped, because ('times > 5')
```

You can override the `unless` by specifying a `--force` to the command line. All conditional tasks are executed and the
unless checks are skipped:

```shell
$ z say 6 --force
Hello!
Hello!
Hello!
Hello!
Hello!
Hello!
```

## Conditional task lines ##

You can add a conditional to a single task line as well. This works similar to the unless statement, but is usually only
done for verbosity checks or configuration checks. A conditional task line is prefixed with `?(expr)`, where expr can be
any valid Z expression.

```
tasks:
    say:
        set:
            times: ? 2
        do:
            - for i in $$(seq 1 $(times)); do echo "Hello!"; done;
            - ?(verbose) echo "I just said Hello $(times) times"
```

```shell
$ z say
Hello!
Hello!

$ z say --verbose
[say] Hello!
[say] Hello!
[say] I just said Hello 2 times
```

To have the line executed only when forced (just as the `unless` section), you can use the `force` variable:

```
tasks:
    try:
        do:
            - ?(force) echo "Try not. Do or do not. There is no try."
```

```shell
$ z try
$ z try --force
Try not. Do or do not. There is no try.
```

Note that both the conditional task lines and the conditional tasks are runtime evaluations. This means that if you
explain the command, they will differ if the outcome of the conditions differ:

```shell
    $ z --explain try
    $ z --explain try --force
    (  echo "Try not. Do or do not. There is no try." );
```

## Using logical expressions as conditions ##
You can use simple logic in these expressions, similar to what you're used to in php:

```
tasks:
    answer:
        # Don't actually do this. It's weird. But.... you could. If you were just a weird man.
        unless: explain
        do:
            - ?(!(force || verbose) ) echo "Please pass either --force or --verbose or both, just for kicks!"
            - ?(force) echo "May it be with you"
            - ?(verbose) echo "Now printing 42...."
            - ?(force && verbose) echo "You just gone option-crazy dude!"
            - ?(42) echo "42"
```

```shell
$ z answer
Please pass either --force or --verbose or both, just for kicks!
42

$ z answer --force
May it be with you
42

$ z answer --force --verbose
[answer] May it be with you
[answer] Now printing 42....
[answer] You just gone option-crazy dude!
[answer] 42

$ z answer --verbose
[answer] Now printing 42....
[answer] 42

$ z answer --explain
( echo "answer skipped, because ('explain')" );

$ z answer --explain --force -- verbose
[answer] (  echo "May it be with you" );
[answer] (  echo "Now printing 42...." );
[answer] (  echo "You just gone option-crazy dude!" );
[answer] (  echo "42" );
```

# Using expressions in your script snippets #

As you have seen before, you can use variables in your script snippets by enclosing them in `$(...)`. This is called a
"script expression". These expressions can contain logical and string operations and functions that are provided by
plugins.

```
tasks:
    talk:
        set:
            what: ?
        do:
            - |
                echo "$(
                    what == "hello"
                    ? "hello to you to!"
                    : "I don't know what " . what . " means, sorry..."
                )"
```

> *Note: this is a perfect example of where the abbreviated syntax for the task body would come in handy. See the
examples for rewritten version of the above task that is more readible*

You can see we utilize the YML extended string syntax here, so we don't get issues with escaping the quotes. The actual
expression that is evaluated is:

```
what == "hello"
? "hello to you to!"
: "I don't know what " . what . " means, sorry..."
```

You must recognize the `... ? ... : ...` ternary operator and the `"string" . "concatenation"` operator from PHP. You
probably noted that the variables aren't prefixed with a dollar sign as in PHP. During the development of Z, I decided
not to use prefixed variables, because they unnessecarily clutter the code.
