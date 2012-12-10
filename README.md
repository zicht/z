z is the universal Zicht developer tool. It is used to create projects, release management, deployment and more.

 The following commands are available:

 v(ersioning)
    :changelog [version1] [version2]

    If not supplied, the defaults are:
        version2 The local working copy version if available. If not available, it defaults to 'trunk'
        version1 is resolved to the latest tag available in the repository

    Versions can be supplied as such:
        'trunk', which is equal to "dev-master" or "dev-trunk" or "dev"
        'branches/branchname', which is equal to dev-branchname

        Default resolution is to tagnames. Resolving a tagname works as such:
        Any tag is matched to the list of tags using standard version numbering. E.g., version "2" would match the
        latest 2.x tag that is not marked RC, alpha or beta
        The tag may contain 'x' or '*' where any substition may be done. In this case, there is no minimum stability,
        which causes to match anything, e.g. 2.1 would match 2.1.0, 2.1.3, but not 2.1.0-RC3, but 2.1.x would.

 p(roject)
    :list

        List a set of projects from the globally registered project list providers

    :setup

        Creates a project from the globally registered set of project templates.

    :info

        Shows information based on the current working directory.


