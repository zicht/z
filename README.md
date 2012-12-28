# Z #
z is the universal Zicht build tool and release management tool.

## Getting started ##
Get into your project root and run

    z init

You will be presented with a few questions to initialize your z.yml file. This file must contain at least the following
data:

    vcs:
        url: "svn://url/to/your/vcs"

    env:
        some_remote_env:
            ssh:    "user@remotehost"
            root:   "root@remotesite"

For some tasks and/or configurations you will additionally need a remote database name, relative web root, and url:

    env:
        some_remote_env:
            url:    "http://my-site/"
            ssh:    "user@remotehost"
            root:   "~/my-app/"
            web:    "web"
            db:     "my_remote_db"

The "some_remote_env" would typically be testing, staging and production.

## Common command overview ##
The following commands are currently supported.

### build ###
Create a build for a specific environment

### deploy ###
Deploy a release to a specific environment

### simulate ###
Simulate a deploy to a specific environment.

### content.backup ###
Create a local backup archive of remote content (database + content assets)

### ssh.init ###
Copy your key to the remote system using ssh-copy-id.

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


