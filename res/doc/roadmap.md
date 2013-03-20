% Roadmap

# Overall #

All upgrade instructions are available in the UPGRADING document. Usually no BC breaks will be done in a minor version
increment. If there are BC breaks planned for a next version, you will be warned about this with a DEPRECATED message.

# version 1.1 #
 +  The name for configuring environments will be replaced by "envs" in stead of "env". All usages of env are
    deprecated.

 +  The preferred variable for identifying the target environment will become 'target_env'.

 +  A new syntax for dynamic configuration will be introduced. At lower levels than top, you can now use a variable name
    to expand variables in the scope:

    ```
    set:
        target_env: 'testing'
    do: echo $(envs[target_env].root)
    ```

    Will be expanded to:

    ```
    do: echo $(envs.testing.root)
    ```

    This way, other names can be used to identify configured environment:

    ```
    set:
        local: development
        target_env: ?
    do: echo $(envs[target_env].root) => $(envs[local].root)
    ```

    The expression notation will therefore be similar to Javascript, such that any property can be accessed using the
    dot notation for literal properties and the bracket notation for dynamic properties. Additionally, the expression
    parser will support descent parsing, such that `obj["my string"]` will expand to the property called `my string` of
    the array (or object) `obj`.

    Note that objects are not yet supported in 1.0, so that will be a side effect.

    > *This is implemented using the PropertyPath component of Symfony 2.2. So Z will upgrade to run on the 2.2 branch*

 +  `$(env.root)` and such will be deprecated, so usage of `env.property` will be wrapped in a separate function as
    a convenience, which will resolve to `envs[target_env].root`, and it will trigger an E_USER_DEPRECATED message.
    As such, all 'set' definitions using 'env' will be deprecated as well, internally rewriting them to 'target_env'.
 +  The plugins will be removed from the default installation of Z and become a composer suggestion for the tool. It
    will get its own version tree and history, and be removed from releases of Z altogether.
 +  An additional "assert" will be added to tasks, which will cause a RuntimeException to be thrown if the assertion
    fails. The assertion is called after 'unless' evaluation, which is right after the execution of the "pre" section,
    and right before the "do".

# version 1.2 #

 +  Remove BC code for env variable usage.

# version 2.0 #

 +  YAML will be replaced by a parser written entirely for Z, to get rid of the quirky YML vs Z syntax issues, such as
    quoting strings. The main internal processing using Symfony\Component\Config will probably stay, though.
