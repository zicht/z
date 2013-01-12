# FAQ #

Why not Make, Pake, Ant, Phing, Rake, etc?
Let me first say this. In the end it really is a matter of taste, and what you feel comfortable with. So if
you don't share my opinion, that is fine. I did not feel comfortable with any of these tools, though GNU Make
has been my favorite for a long time.

The power of Z is it's simple implementation and it's expressive syntax. By utilizing Pimple and the Symfony
Config, Yaml and Console components, combining it with a very simple yet powerful plugin mechanism, it is much
easier to adapt and implement than many of the other tools out there. It is also easy to share your own recipes,
because of it's ultimately simple YML-based configuration syntax. It depends on practically nothing but the mentioned
components and a shell, and they are all at our disposal. Furthermore, if you ever used bash, you can use Z, you
don't need knowledge of much of the Z syntax to get started real quick.

Why is it called Z?
We use Z for build, release and integration management at the company where it was originally developed: Zicht Online.
Note that the logo is very similar ;)

Under what terms may I use and distribute Z?
Z is distributed under the MIT License. This means that you can do whatever you like with it, provided you include
the license and copyright notices.

Where can I find recipes?
A substantial list of recipe examples is included in the source code. These recipes are provided as plugins, and are
all enabled by default. See the documentation on Plugins how to enable, disable or extend these plugins.

Why is there no default git plugin?
We don't use git as our primary VCS. This means that we can't test and utilitize it for real, which means that we
can't be sure about how well it would work. But since the system is plugin-based, you could easily build and contribute
your git implementation. Drop us a note if you have.

How to get started
All you need to get started is a working version of Z installed somewhere on your system, and the bin file in bin/z
on your system path. You will now have Z available to run as such:

    $ z

The output will show you what available tasks and commands you have at your disposal, and how to run them. By default,
Z assumes that you will want to use it as a build and deployment tool. As such, you will want to be able to build and
deploy projects and put the configuration of these builds in a simple file, called a Z file. At the core, there is no
dependency on this file, but there is always project specific data you will need. This is where the 'z:init' command
comes into play:

    $ z z:init

This will guide you to a set of questions you need to answer to get a z.yml file in your working directory. When
finished, you will have a file you can use for deployment.

    $ z simulate --env=testing

This will simulate a deploy to the environment specified as 'testing' in your setup process. If you are wondering what
the tasks would exactly do, use the --explain option:

    $ z --explain simulate --env=testing

How to expose a plugin to Z
A plugin consists of at least one of these two files. A z.yml file, and/or a Plugin.php file. Additionally, it may
contain a options.yml file, which is a definition of the options that the plugin allows. You can see the core plugins
to see what the possibilities are.

Plugins may be defined in a variety of ways. The priority of each of this methods is as follows:

First, a system file called ZPREFIX/etc/plugins.yml is read. The plugins configured in this file will be available to
any instance of Z running. Then, if exists, a user config file called ~/.config/z/plugins.yml is read. If this file
exists, it will prevail over the default plugins, i.e. all default plugins will be ignored and only the plugins in this
file are used.

A plugin definition consists of:

* A name
* A Plugin.php file
* An options.yml file
* A z.yml file

This can be short-cut as follows: '/path/to/my-plugin' in which case the name is expanded as "my-plugin", the
Plugin.php is expanded as "/path/to/my-plugin/Plugin.php", and so forth for the other two files. By default, if the
dir not absolute, it can be relative to the working dir, or to the Z installation's plugin path. This means that if you
specify 'rsync' as the plugin, and you execute z from your homedir, it is assumed to exist in /home/user/rsync/. If
that dir not exists, it is assumed to be in ZPREFIX/plugins/rsync.

How to use Z for continuous integration

Create an integrate task in your z.yml:

tasks:
    integrate:
        pre: !(dir vendor) composer install
        do:  @qa

Now, Z will need a periodical task to check out the working copy, run the integrate task, and save the reports somehow. This is simple using a cronjob:

10 * * * * z --config=/usr/local/etc/z/integration.yml integrate

The integration yml will look like this:

tasks:
    integrate:
        set:
            vcs.uri: $(projects
svn checkout $(vcs.url)/$(vcs.version) $(tmpdir)
        do:  cd $(tmpdir); z integrate


Note that the project's integrate command will run in a subshell, and therefore only uses the z file that is exposed
inside the project. This way, the processes will be independent of each other.

Using conditional execution:
for each line, a conditional execution maybe done. By default, a conditional execution is of the following form:

unless: isset $(yield)

This means that if the value of the yield is already set in the current execution scope, the task is skipped. You can
apply conditional tasks or lines inside a task by adding a conditional to the beginning of the line:

!(dir build) mkdir build

Of course, this example is not very sensible, as you can pass a -p to the mkdir shell command any way, but it comes more
apparent if you do something like this:

!(versionof $(build.dir) == versionof .) svn checkout $(vcs.version)

    NOTE: for convenience, you can write the same like this:

svn checkout $(vcs.version) unless (versionof $(build.dir)
