# Z 

## Introduction ##
Z is a metaprogramming system, especially useful for dependency-based build and
deploy management. It was originally designed for PHP-based projects. 

It utilizes a simple declarative DSL, based on YML, to define tasks and
configuration. The configuration can be used (and validated) by plugins, that
provide their own set of tasks and / or language extensions.

But, all that put aside, Z is mainly a very convenient way to organize
any set of commands you'd typically run in a shell.

## Further reading

* Follow the [tutorial](tutorial.md) to get a feel for how it works.

Tasks are defined by prerequisites, a body identified by the "do" section and
task triggers, which trigger commands and/or other tasks right after the task
is executed.

* [Tutorial](tutorial.md)
* [Reference](reference.md)
* [Advanced usage](advanced.md)
* [plugins](plugins.md)
* [Running Z files standalone](standalone.md)
* [roadmap](roadmap.md)
* [FAQ](faq)
