# Z #
z is the universal Zicht build tool and release management tool.

## Getting started ##
Get into your project root and run

    z z:init

You will be presented with a few questions to initialize your z.yml file. This file must contain at least the following
data:

    vcs:
        url: "svn://url/to/your/vcs"

    env:
        some_remote_env:
            ssh:    "user@remotehost"
            root:   "~/path/to/the/projects/root"

For some tasks and/or configurations you will additionally need a remote database name, relative web root, and url:

    env:
        some_remote_env:
            url:    "http://my-site/"
            ssh:    "user@remotehost"
            root:   "~/my-app/"
            web:    "web"
            db:     "my_remote_db"

The "some_remote_env" would typically be testing, staging and production.

## Prepare for deployment

To prepare for deployment, it's best to issue your SSH key to the remote machine to avoid having to enter passwords
every time the tool connects to the remote machine. You can accomplish this by running

    z env:ssh:init --env=staging

An `ssh-copy-id` command will be executed to copy your public key to the remote machine. After this, you enter a
remote shell by calling

    z env:ssh --env=staging

The shell will be started at the remote machine and you will be in the remote root.

## Simulate a deploy

The `simulate` task will execute a build and simulated sync. The version that will be deployed is specified in your
YML file under vcs.version. By default, this is set at trunk@HEAD. To deploy a different branch, you should specify
the version in your z.yml file as such:

    vcs:
        version: branches/maintenance@HEAD

## Deployment

When you are confident that the simulation presents the correct information, you should be able to deploy the build
to the remote machine.

(Note: Currently, the build is reissued, but in future versions the version of the build will be compared to the
version to be deployed, so the build step will be skipped if they are the same).

## Explaining or stepping through commands
You can pass a `--step` or `--explain` option to see exactly what a task will do. Be careful with stepping, because
it might break the entire process, if you skip a step. The --explain option will show all shell

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
Log in to the remote mysql shell. The task assumes that the remote username and password are set in the `~/.my.cnf`
so that the user has access to the database specified in the given environment.


## Z commands ##

There are some commands used for the Z internals. These are all prefixed with the 'z:' prefix.

### z:dump ###
This dumps the used configuration as a YML file. If the --verbose option is passed, the container (excution context
based on Pimple) is also dumped. The container is initialized using a JIT compilation of the
available configuration options and tasks in a Pimple service container.

### z:init ###
Create a basic Z file inside a project.


