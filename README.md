# Z #
z is the universal Zicht developer tool. It is used to create projects, do release management, deployment and more.

## Common command overview ##
The following commands are currently supported.

### build ###
Create a build for a specific environment

### deploy ###
Deploy a release to a specific environment

### simulate ###
Simulate a deploy to a specific environment. The only difference with an actual deployment is that the 'post' tasks
may differ, and that the synchronization of the data is done with a --dry-run flag.

### content.backup ###
Create a local backup archive of remote content (database + content assets)

### ssh ###
Log in to the remote shell

### mysql ###
Log in to the remote mysql shell

## Z commands ##

There are some commands used for the Z internals. These are all prefixed with the 'z:' prefix.

### z:explain ###
This command shows all shell commands that would have been executed if the given task was run. This is 
useful to inspect what the actual internals are, and if all the variables and dependencies that are 
involved can be resolved.

### z:dump ###
This dumps the internal container. The container is initialized using a JIT compilation of the
available configuration options and tasks in a Pimple service container. This command dumps the
initialization PHP code.

### z:init ###
Create a basic Z file inside a project.


