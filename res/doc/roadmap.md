# Roadmap for Z #

One important goal for any Z upgrade is providing conversion scripts for the yml files. This will only be done for minor
and major releases, e.g. 1.0 => 1.1 or 1.2 => 2.0, and they will all be incremental.

## version 1.1 ##
- A new syntax for dynamic configuration will be introduced. At lower levels than top, you can now use a variable name
  to expand variables in the scope:

  ```
  set:
     env: 'testing'
  do: echo $(env.$env.root)
  ```

  Will be expanded to:

  ```
  do: echo $(env.testing.root)
  ```

  This way, other names can be used to identify configured environment:

  ```
  set:
      local: development
      env: ?
  do: echo $(env.$env.root) => $(env.$local.root)
  ```

- $(env.root) and such will be replaced by $(env:root), which is syntactical sugar for $(env.$env.root). This
  eliminates the select('env') call in the setup of the commands. This way, other dynamic configuration can be used
  in the future. The 'select' call will be deprecated and removed in 1.2, so usage of 'env.property' will be wrapped
  in a separate declaration, which will trigger an E_USER_DEPRECATED message.
- The plugins will be removed from the default installation of Z and become a composer suggestion for the tool. It
  will get its own version tree and history, and be removed from releases of Z altogether.
- The shell will be configurable per command line, syntax yet to be decided. Probably there will be a separate shell
  factory which can be used to specify the shell, so that any plugin can provide the actual shell to use in a command,
  such as:

  ```
  - Command         => env.remote(env)
  - Local command   => "/usr/local/bin/zsh"
  - Query           => "/bin/mysql"
  ```

  In the above examples, env.remote(env) would yield something like 'ssh user@host'.

  The downfall is that ssh connection can no longer be used with passwords, but this is something to avoid anyway.

## version 1.2 ##
- Remove the select() call for environments entirely and remove BC code for env variable usage.

## version 2.0 ##
- YAML will be replaced by a parser written entirely for Z, to get rid of the quirky YML vs Z syntax issues, such as
  quoting strings. The main internal processing using Symfony\Component\Config will probably stay, though.


