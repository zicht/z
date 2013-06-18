% Continous integration

# Jenkins #
You can use Z to integrate your projects in Jenkins. The easiest way to do this, is configure a default z.yml in
`jenkins`'s homedir at `~/.config/z/z.yml`. Of course, you will need the standard plugin repository available on the Z
plugin path.

Add the following like to the file:

~~~~yml
plugins: ['ci']
~~~~

and make sure the plugin is loaded:

~~~~shell
z list ci
~~~~

Try explaining a build inside a jenkins workspace, such as `/var/lib/jenkins/jobs/my-job/workspace`

~~~~shell
z ci:build --explain
~~~~

The output will display all commands used to do an integration build. The following tools and tasks are implemented by
default. Most of these tools will use a sensible default configuration to allow the build step to run without separate
configuration. If you would like to configure or alter this globally, you can either add it to `~/.config/z.yml`,
add your custom plugin there or for your own plugin. Of course, you could also add a custom config to your project.

## Subtasks ##

The following subtasks are available. The build is failed when the shell returns an exit code for the `lint`, `phpunit`
and `phpdox` subtasks

--------------------- ----------------------------------------------------------- ---------------------  --------------
Tool                  Description                                                 Default config?        Fails build?
--------------------- ----------------------------------------------------------- ---------------------- --------------
Lint                  PHP's command line lint tool is executed for all files in   n/a                    Yes
                      `src/` and `tests/`

PHP Mess Detector     Runs phpmd and outputs build information the same way as    Yes (`phpmd.xml`)      No
                      Bergmann's Ant job

PHP_Depend            Runs the pdepend tool against the `src/` directory          n/a                    No

PHP_CodeSniffer       Runs phpcs against the configured standard. This can be     n/a                    No
                      put in `ci.phpcs.standard`.
                      By default, only the src directory is checked.

PHPUnit               Runs the test suites in `tests/`                            Yes (phpunit.xml.dist) Yes

phploc                Generates code metrics reports using phploc                 n/a                    No

phpcpd                Runs the php copy detector tool                             n/a                    No

phpdox                Generates API documentation using phpdox                    Yes (phpdox.xml.dist)  Yes

phpcb                 Generates HTML for the source files                         n/a                    No
--------------------- ----------------------------------------------------------- ---------------------- --------------

The default config files are distributed with the ci plugin. Each of the jobs which provide a default config copies
the config file from the plugin to the root of the build, before running the task. If the files are in place already,
they are not copied, so you can provide your custom project config as well. You can test this by running the `ci:skel`
command.

## Configuration options ##

~~~~yml
ci:
    lint:       true
    phploc:     true
    pdepend:    true
    phpmd:      true
    phpcs:
        enabled:  true
        standard: Zicht
    phpcpd:     true
    phpdox:
        enabled:    true
        file:       ""
    phpcb:      true
    phpunit:
        enabled: true
        opts:   []
~~~~
