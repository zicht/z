# Z #
z is the universal Zicht developer tool. It is used to create projects, do release management, deployment and more.

## Command overview ##
The following commands are currently supported.

### release:build ###
Create a build for a specific environment

### release:deploy ###
Deploy a release to a specific environment

### release:simulate ###
Simulate a deploy to a specific environment. The only difference with an actual deployment is that the 'post' tasks
may differ, and that the synchronization of the data is done with a --dry-run flag.

### content:backup ###
Create a local backup archive of remote content (database + content assets)

### content:push ###
Push a local backup archive to a remote environment

### env:ssh ###
Log in to the remote shell

### env:mysql ###
Log in to the remote mysql shell

### util:apc_clear_cache ###
Flush the remote APC cache