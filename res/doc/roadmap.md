# Roadmap for Z #

## version 1.1 ##
- A new syntax for dynamic configuration will be introduced. At lower levels than top, you can now use a variable name
  to expand variables in the scope:

      set:
         env: 'testing'
      do: echo $(env.$env.root)

  Will be expanded to:

      do: echo $(env.testing.root)

  This way, other names can be used to identify configured environment:

      set:
          local: development
          env: ?
      do: echo $(env.$env.root) => $(env.$local.root)

- $(env.root) and such will be replaced by $(env..root), which is syntactical sugar for $(env.$env.root). This
  eliminates the select('env') call in the setup of the commands. This way, other dynamic configuration can be used
 in the future. The 'select' call will be deprecated and removed in 1.2
- The plugins will be removed from the default installation of Z and become a composer suggestion for the tool. It
  will get its own version tree and history, and be removed from releases of Z altogether.

