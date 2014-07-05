# Upgrading to 2.0 #

## Upgrade script ##
There is a very simple (and therefore error prone) substition script available which replaces some common 1.0 features
with their 2.0 equivalent.

## Core ##
* All 1.0 backwards compatibility was removed:
    * 'env' is no longer a global setting. The 'env' plugin provides an "envs" setting, which can be used to store
      environment settings.
    * The 'env' global was removed. You now need to refer to environment settings by using a key (by convention
      'target_env'). E.g. `env.ssh` must be replaced by `envs[target_env].ssh`, and any `env` parameter should be
      replaced with something else, in this case `target_env`.
    * `set` is no longer a task settings. You should replace it with `args` (for the same behaviour) or you may
      introduce `flags` or `opts`.
    * The global `verbose` and `explain` options' values were renamed to `VERBOSE`, `EXPLAIN` and `FORCE`
* A version check was added. You should add a line containing "# @version '>=2.0,<3'" to make sure a compatible Z core
  is used.

## Plugins ##
* The `core` plugin no longer exists. It used to contain the `deploy` and `simulate` tasks, which are now moved to a
  separate `deploy` plugin