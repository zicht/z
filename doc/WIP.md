# FAQ #

## Why not Make, Pake, Ant, Phing, Rake, etc? ##
Let me first say this. In the end it really is a matter of taste, and what you feel comfortable with. So if
you don't share my opinion, that is fine. I did not feel comfortable enough with any of these tools, though GNU Make
has been my favorite for a long time.

The power of Z is it's simple implementation and it's expressive syntax. By utilizing the Symfony
Config, Yaml and Console components, combining it with a very simple yet powerful plugin mechanism, it is much
easier to adapt and implement than many of the other tools out there. It is also easy to share your own recipes,
because of it's ultimately simple YML-based configuration syntax. It depends on practically nothing but the mentioned
components and a shell, and they are all at both our disposal. Furthermore, if you ever used bash, you can use Z, you
don't need knowledge of much of the Z syntax or architecture to get started real quick.

## Why is it called Z? ##
We use Z for build, release and integration management at the company where it was originally developed: Zicht Online.
Note that the logo is very similar ;)

## Under what terms may I use and distribute Z? ##
Z is distributed under the MIT License. This means that you can do whatever you like with it, provided you include
the license and copyright notices.

## Where can I find recipes? ##
A substantial list of recipe examples is included in the source code. These recipes are provided as plugins, and can
be used in your configation simply by specifying them in your Zfile. See the documentation on Plugins how to enable,
disable or extend these plugins.

## Why is there no default git plugin? ##
We don't use git as our primary VCS. This means that we can't test and utilitize it for real, which means that we
can't be sure about how well it would work. But since the system is plugin-based, you could easily build and contribute
your git implementation. Drop us a note if you have. No, really (!).

# How to get started #
All you need to get started is a working version of Z installed somewhere on your system, and the bin file in bin/z
on your system path. You will now have Z available to run as such:

    $ z

The output will show you what available tasks and commands you have at your disposal, and how to run them. By default,
no tasks or configuration is done. This is all done through the usage of plugins. You can find documentation on the
plugins separately.

A sane setup for z would be as follows:

    plugins: ['core', 'svn', 'rsync']

    vcs:
        url: svn://my/project

    env:
        production:
            ssh: myuser@remotehost
            root: ~/app/deploy-dir

This would provide your setup with a possibility to build and deploy to a remote ssh host identified by 'production'.
Run the command

    $ z simulate production

To simulate a deploy. To see what exactly would be done, you can use the --explain flag:

    $ z simulate production --explain

This will guide you to a set of questions you need to answer to get a z.yml file in your working directory. When
finished, you will have a file you can use for deployment.

    $ z simulate testing

This will simulate a deploy to the environment specified as 'testing' in your setup process. If you are wondering what
the tasks would exactly do, use the --explain option:

    $ z --explain simulate testing

## How to expose a plugin to Z ##
A plugin consists of at least a z.yml file, and optionally a Plugin.php file. You can see the core plugins
to see what the possibilities are.
