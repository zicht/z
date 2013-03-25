% The Z app builder

As of version 1.1, Z comes with an application builder that you can use to distribute your own command line helper
applications as a single archive without further configuration. You can use this to automate simple tasks for your
colleagues, without having them to tamper in the configuration files of the source distribution.

There is a file called 'package.php' in the bin directory of the Z source tree. You can call this file from the command
line and pass some options to build your own Z-based CLI application. You don't even need to call it Z anymore, you
can call it whatever you like, add in your own version string and application name, and delete all traces of Z. Except
of course the LICENSE notices inside the phar, because otherwise it would break it's own license conditions. And that,
we do not want. ;)

Please let us know if you have built something cool with Z!

# Usage #

package.php comes with a variety of options to control the build output:

  + `--app-name`: controls the application name mentioned in the CLI output header
  + `--app-version`: controls the version string mentioned in the `help` and `version` commands
  + `--config-filename`: In a dynamic build, use this to control the filename Z looks for, if you don't want to use
    `z.yml`. This is typically useful if you want to use separate versions of Z which look for different file names.
  + `--static`: Create a static build of Z, i.e., it is entirely self contained and will include the compiled code. You
    must provide the config file you want to include in the build. Plugins will be resolved automatically and added to
    the build as well. When using such build, it is no longer possible to modify your z config afterwards, so the
    application of this is entirely different from using Z files in your projects for example.

# When you would use a static build #

If you want to have a tool that is not called Z, does some common tasks, but you want to write the code in Z, for
example, starting a simple project based on composer:

    tasks:
        create:
            help: "Create a project based on a local project template"
            set:
                dir: ?
            do:
                composer create \
                    zicht-project-templates/symfony-standard \
                    $(dir) \
                    --repository-url=http://our-composer-repository/ --stability=dev
         update:
            help: "Update your working copy"
            do:
                - svn up
                - composer install

Now package this as the tool "project":

    php path/to/z/bin/package.php build project --static=projects.yml \
        --app-name="Our Cool Project Tool" --app-version="dev-master"
    cp ./project /usr/local/bin/
    project create my-new-project

This makes automation from the command line so simple, that I wish I hadn't told you all this.