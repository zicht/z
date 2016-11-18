# Upgrading to 3.0 #

## Z 3.0 introduces a new internal parser replacing Yaml as a base format ##

### Why was this necessary? ###
It wasn't. But, because Z's 1.0 and 2.0 incarnations were both based on YAML as the primary input format, it was very
hard to report back on errors that were inside the YAML tree, since Symfony's Yaml component did not keep track of
offsets while parsing.

Moreover the syntax had to be Yaml first, and Z later. This meant that in some occasions you had to use weird constructs
such as doubly quoted strings.

### What changed in syntax? ###

Not much. There's a few improvements and some caveats, though.

#### Block strings ####

 It is no longer required to use the Yaml style block strings. There is a few ways you can define a block string:

```
  a: |
   old "yaml"-style
   block string

  b:
     new format starting
     at a new indent

  c: you may also continue
     at the same
        (or deeper) indent

#### No more string vs value confusion ####

You may no longer need to quote elements that are an expression. In fact, that will now render an error.

  tasks:
    foo:
      yield: "foo"

#### When inlining arrays or hashes, you must quote all your data ####

In yaml, this is valid:

   foo: {key: value}

This is not longer supported. These expressions are now parsed using the expression parser:

   foo: {key: "value"}

This leads to cleaner code anyway, and is still compatible with yaml.
