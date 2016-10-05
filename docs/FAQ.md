% FAQ

# How do you pronounce Z? #
You pronounce it "Zee", and it rhymes with Bee, the one that goes buzzzzzzz.

# Under what terms may I use and distribute Z? #
Z is distributed under the MIT License. This means that you can do whatever you like with it, provided you include
the license and copyright notices, and you don't come banging on our door if it didn't work the way you expected. There
is no warranty, express or implied, and any documentation or source file stating otherwise must be considered invalid.

# Why not Grunt, Make, Pake, Ant, Phing, Rake, Gulp, etc? #

Or: "Why not Capistrano, Capifony, Puppet, Chef, Ansible, etc?"

Let me first say this. In the end it really is a matter of taste, and what you feel comfortable with. So if
you don't share my opinion, that is fine. I did not feel comfortable enough with any of these tools, though GNU Make
has been my favorite for a long time.

The power of Z is it's simple implementation and it's expressive syntax. By utilizing the [Symfony](http://symfony.com/)
Config, Yaml and Console components, combining it with a very simple yet powerful plugin mechanism, it is much
easier to adapt and implement than many of the other tools out there. It is also easy to share your own recipes,
because of it's ultimately simple YML-based configuration syntax. It depends on practically nothing but the mentioned
components and a shell, and they are all at both our disposal. Furthermore, if you ever used any shell, you can use Z,
you don't need knowledge of much of the Z syntax or architecture to get started real quick.

Z relies heavily on a shell to do the heavy lifting. If you're not comfortable with bash, however, Z is not limited to
bash as a primary interpreter. You can extend Z easily by utilizing any shell that is capable of processing stdin. You
can even alternate between shells in different parts of your recipe.

# Why is it actually different than any of the aforementioned tools? #
Z is a set of building blocks for generating executable code by your shell. In short, this means that Z does not
run anything, it generates shell commands which do the actual work. For example, if you need to transfer
files from one system of another, many utilities are available for that already. If you are used to using FTP for this,
you can use Z, if you're used to using rsync over SSH, you can use Z. If you need to mount remote filesystems first and
then use rsync, you can use Z. You don't need to know Z for it, you will just need a shell command. This makes Z closer
to the UNIX principle than any other tool out there.

The idea is that all you need is a tool to orchestrate these shell commands and help you in structuring your 
dependencies. In that respect, it is closest to `make` than any other tool. The main difference with make is that
make targets building one set of files from another, while Z targets the execution of tasks. I have, however, on a
number of occasions used make together with Z.

# Why is it called Z? #
We use Z for build, release and integration management at the company where it was originally developed:
[Zicht online](http://zicht.nl). Z was developed by [Gerard van Helden](http://melp.nl), lead developer at Zicht online.

You might notice the similarity in logos.

# Where can I find recipes? #
A substantial list of recipe examples is available as a separate package. These recipes are provided as plugins, and can
be used in your configation simply by specifying them in your Zfile. See the documentation on Plugins how to enable,
disable or extend these plugins.

# Why is there no default git plugin? #
We don't use git as our primary VCS (yet). This means that we can't test and utilitize it for real, which means that we
can't be sure about how well it would work. But since the system is plugin-based, you could easily build and contribute
your git implementation. Drop us a note if you have, because we would love to integrate it!

# How can I get started the quick way? #
All you need to get started is a working version of Z installed somewhere on your system, and the bin file in bin/z
on your system path. You will now have Z available to run as such:

```shell
$ z
```

The output will show you what available tasks and commands you have at your disposal, and how to run them. By default,
no tasks or configuration is done. This is all done through the usage of plugins. You can find documentation on the
plugins separately.

A sane deployment setup for z would be as follows:

```
plugins: ['deploy', 'svn', 'rsync']

vcs:
    url: svn://my/project

envs:
    production:
        ssh: myuser@remotehost
        root: ~/app/deploy-dir
```

This would provide your setup with a possibility to build and deploy to a remote ssh host identified by 'production'.
Run the command

```shell
$ z simulate production
```

To simulate a deploy. To see what exactly would be done, you can use the --explain flag:

```shell
$ z simulate production --explain
$ #or explain the actual deploy
$ z deploy production --explain
```

Read the [tutorial](tutorial.html) for a more detailed walkthrough of how Z works.