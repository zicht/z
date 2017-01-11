# Reference


## Task definition ##

A task, by default, has the following structure:

```
tasks:
    namespace.taskname:
        help: |
            Description
    
            More thorough description
    
            foo: describe the argument "foo"
            --bar: describe the option or flag "--bar"
        args: 
            foo: expression
            bar: ? expression
            baz: ?

        flags:
            foo: false
            bar: true

        opts:
            foo: expression
            bar: expression
       
        unless: expression
        if: expression
        assert: expression

        set:
            foo: expression
            bar: expression

        pre:
            - @another.task
            - Some shell script make use of variable $(expression)
            - @(if expression) Some conditional shell script

        do:
            - @another.task
            - Some shell script with a $(expression)
            - @(if expression) Some conditional shell script

        post:
            - @another.task
            - Some shell script
            - @(if expression) Some conditional shell script

        yield: expression
```

### `args`
Arguments can be defined using a name (the key), an optional question mark and
an optional default value:

* If the argument has a question mark, the input can be provided from the
  command line. 
* If a default value is passed, the argument is optional

The names of the arguments can be used in any subsequent expression.

### `flags`
Flags are "on-or-off" switches that can be passed into the task using
commandline flags. These flags are exposed in two ways, either `--flag` or
`--no-[flag]`, which respectively means that the flag is set to true or false.
If the flag is not passed, it's default value (the one from the definition) is
used.

The value of the flag can be used in any one of the subsequent expressions.

### `opts`
Options  can be passed in through the command line in the form
`--option=value`. They behave similarly to arguments in the way that they can
either be required or not (though it is considered bad practice to have
non-optional options. You may understand the confusion ;)).

### `set`
All declarations done in the `set` are local variables. This is only for
convenience, so you don't have to repeat expressions within your task, for
example:

```
tasks:
    refresh:
        args:
            some_file: ? "foo"
        set:
            _path: path("/tmp", some_file)
        do: 
            - rm $(_path) && touch $(_path);
```

As a best practice, local variables are prefixed with an underscore, unless
they serve as input for another task.

### `unless`
Don't run the task's `do` and `post` section when this expression evaluates to
`true`.

### `if`
Only run the task's `do` and `post` section if this expression evaluates to
`true`.

Though you may supply both `unless` and `if`, it's best to choose one that best
fits your need. For example `unless: is_file("foo")` is arguably better legible
than `if: !is_file("foo")`. On the other hand, `if: confirm(...)` is better than
`unless: !confirm(...)`.

### `assert`
Assertions can be used to be very forcible about a certain assessment. In other
words: if the assertion fails, all else should fail as well. A good example of
this could be asserting that the task is not being run as root.

### `pre`, `do` and `post`
These three sections can be used to write a set of shell commands to be run or
refer other tasks with the `@` sign.

The sections can be arrays of task lines, or just one single task line. A task line
can be either a piece of shell code, or a reference to another task.

Usually, three forms are commonly seen:

```
do: a single line shell command
do: |
    a multiline
    shell command
do:
    - first single line shell command
    - second single line shell command
```

`pre` and `post` more often refer to other tasks:

```
pre: @some_dependency
post: @something_that_should_be_triggered
```

A very distinct difference between `pre` and `post` on the one hand and `do` on
the other, is that `pre` and `post` can only be appended to, and `do` always is
overwritten when specified.

For example:

```
# z.yml
imports: ['./import.yml']

tasks:
    test:
        pre: echo "Pre from z.yml"
        do: echo "Hello from z.yml"
        post: echo "Post from z.yml"
```

```
# import.yml
tasks:
    test:
        pre: echo "Pre from import.yml"
        do: echo "Hello from import.yml"
        post: echo "Post from import.yml"
```

Running the `test` task within a shell:
```
$ z test
Pre from import.yml
Pre from z.yml
Hello from z.yml
Post from import.yml
Post from z.yml
```

### `yield`
Defines what is the "yield" of the task. If the task has a yield, it is not
executed again. It is important to understand, though, that even though the
arguments to the task might change, it's yield is considered something that can
not. So be careful how you craft your tasks, because yielding a value is not
the same thing as a `return` in a procedural style. Combining a yield with
arguments is not necessarily always a bad idea, but you should only do that if
the argument is passed to Z at a global level, i.e.: it will not change during
the run time.

#### Using an arbitrary `yield`
You can use `yield` with a value of `true` to simply identify that this task
should not be run again:

```
tasks:
    _create_dir:
        do: mkdir -p ./some/path
        yield: true
```

#### Reusing the yield
This is a very typical pattern you might find useful. 

```
tasks:
    _cache:
        set:
            _cache_file: path("/tmp/some_cache_file")
        do: something_expensive > $(_cache_file)
        yield: _cache_file

    test:
        - echo "Now reading: $(tasks._cache):"
        - less $(tasks._cache)
```

When running `z test`, the cache is only created once, because the result of
the `yield`, replaces the value of `tasks._cache` in the container.

## Expression syntax
Expressions can be used almost anywhere within Z, but the most common use of
it is with shell commands. The delimiters used within the shell commands are
`$(` and `)`. 

Example:

```
tasks:
    say_hello:
        args: 
            some_arg: ?
        do: echo "Hello $(some_arg)!!" 
```

Running this:

```
$ z say_hello world
Hello world!!
```

You are not limited to only using variables here. Z supports an expression
syntax for various purposes. To test an expression, you can use the `z:eval`
command from the command line, which is very useful if you want to quickly test
out some things, or if you want to read data from the z.yml without
introspecting the yml file. The output is serialized to yml by default.

```
$ z z:eval 'user'
gerard
$ z z:eval 'path("/tmp", "a", "b")'
/tmp/a/b
$ z z:eval 'keys tasks'
- test
$ touch foo && z z:eval 'is_file("foo")'
true
```

The syntax is roughly outlined here in BNF. This is no formal grammar, though,
because the parser was implemented without using a parser generator and the
precedence of the operators is handled by reordering the parse tree after
parsing.

If you are not familiar with BNF, suffice to say that the grammar is very much
similar to languages such as PHP, Javascript and Python.

```
expr ::=
    '(' expr ')'        # expression wrapped in parentheses
    | unary_op expr     # Unary expression
    | term op expr      # Binary expression
    | term              # Term

unary_op ::=
    '!'                 # Logical "not" 
    | '-'               # Arithmetic "negative"
    | '+'               # Arithemtic "positive"

op ::=
    op_comparison       # Comparison operation
    | op_logical        # Logical operation
    | op_arithmetic     # Arithmetic operation


op_comparison ::=       # Typical comparison operators
    '==' | '!=' | '<=' 
    | '>=' | '<' | '>' 

op_logical ::=          # Typical logical operators 
    '||' | '&&' 
    | 'or' | 'and' | 'in' 

op_arithmetic ::=       # Typical arithmetic operators
    '*' | '/' | '+' | '-'

term ::= 
    reference '(' ')'            # invocation
    | reference '(' arg_list ')' # invocation with arguments
    | reference expr             # invocation with only one argument
    | reference                  # variable reference
    | literal                    # literal reference

reference ::=
    name '[' expr ']'           # "dictionary"-style resolving of property
    | name 

name ::=
    local_name
    | namespaced_name

namespaced_name ::=
    local_name '.' name

local_name ::=
    '[_a-z][_a-z0-9]'


literal :==
    string_literal
    | number_literal
    | 'false'
    | 'true'
    | 'null'
```

Some things to note:

* A single-argument call is supported as syntactical sugar, so you can, for
  example, do this: `echo $(escape something)`. 
* There is no string concatenation operator. Usually `sprintf` is the more
  applicable choice, but for regular concatenation you can use `cat`.
* string literals only support *double quoted strings*.
* number literals only support whole numbers and a decimal place; scientific
  notation is not supported

## Task line annotations
A task line annotation can alter the behaviour of the task line. They always
precede the task line and can be combined. The format is as follows:

```
@(name [optional additional syntax])
```

The additional syntax is parsed by the annotation itself and has no specific
syntax. This provides a lot of flexibility for extensions.

### Default annotations
The following annotations are available by default.

| name      | description | 
| --------- | ----------- |
| if        | only execute the task line if the condition evaluates to true |
| for       | repeat the task line for each of the passed values |
| with      | declare a variable for the duration of the task line |
| sh        | use a different shell for this task line |

#### `if`
Use this to skip steps based on simple conditions:

```
tasks:
    readme: @(if is_file("README.md")) cat README.md
```

There is no 'else' construct. If you need such a thing, repeat the task line
and add a ! before the expression to negate it. This keeps the task lines
atomic (as they should) and does not impose complex branching structures.

```
tasks:
    readme: 
        - @(if  is_file("README.md")) cat README.md
        - @(if !is_file("README.md")) echo "There is no README!"
```

If the check that needs to be done is expensive, you can store the outcome in a
variable.

```
tasks:
    readme: 
        set:
            _is_file: is_file("README.md")
        do:
            - @(if  _is_file) cat README.md
            - @(if !_is_file) echo "There is no README!"
```

#### `for` and `each`

You can use `for` and `each` to loop over lists and dictionaries. `each` is
syntactic sugar for a for loop with `_key` and `_value` as the variable names.

```
globals:
    my_todo_list:
        - "Write tests"
        - "Write docs"
        - "Write code"
        - "Drink beer"
tasks:
    todo: @(for id, value in globals.my_todo_list) echo "[$(id + 1)] $(value)"

    # OR:
    todo: @(each globals.my_todo_list) echo "[$(_key + 1)] $(_value)"
```

You may also omit the key in the `for` loop:

```
@(for value in globals.my_todo_list)
```

Running this will repeat the line 4 times with the interpolated values:
```
$ z todo --explain
echo 'echo "[1] Write tests"' | /bin/bash -e
echo 'echo "[2] Write docs"' | /bin/bash -e
echo 'echo "[3] Write code"' | /bin/bash -e
echo 'echo "[4] Drink beer"' | /bin/bash -e
```

A very useful application of looping is declaring some tasks in a specific
namespace and looping over that:

```
tasks:
    _ci.phpunit: ./vendor/bin/phpunit --no-coverage
    _ci.eslint: ./node_modules/.bin/eslint 

    ci: @(for name in keys(tasks._ci)) $(tasks._ci[name])
```
This make use of a very useful feature of Z:

Since all tasks are declared in the `tasks` namespace, and each task is in fact
a declaration within the container (i.e., a closure that can be run),
evaluating the task will run it. In this case, the task has no `yield`, so the
result of the task is an empty string, which is ignored by the "executor",
which would normally pass it to the shell as input:

```
$ z --explain ci
echo './vendor/bin/phpunit --no-coverage' | /bin/bash -e
echo './node_modules/.bin/eslint' | /bin/bash -e
```

#### `with`
With is useful if you want to trigger another task with some additional
parameter, or if you want prepare some variable for use in the task line

```
tasks:
    hello: @(with user as name) echo "Your name is $(name)"
```

Running the task would give:
```
$ z hello
Your name is gerard
```

## Globals reference
The following globals declared:

| name      | description |
| --------- | ----------- |
| `z.opts`  | The currently passed `--force`, `--explain`, `--debug` and `--verbose` flags (as a string) |
| `z.cmd`   | An absolute reference to the Z "binary" that is currently running |
| `now`     | The current date time in format YYYYMMDDHHmmss |
| `cwd`     | Current working directory. Note that this is a compile time declaration and always contains the working directory of where Z was started | 
| `user`    | The current user (shell $USER) |
| `confirm` | returns `false`. Include the `interact` plugin for interactive confirmation |

## Function reference

### I/O

| name      | description |
| --------- | ----------- |
| `is_file(path)` | PHP's `is_file` |
| `is_dir(path)`  | PHP's `is_dir`  |
| `mtime(path)`   | PHP's `filemtime` |
| `atime(path)`   | PHP's `fileatime` |
| `ctime(path)`   | PHP's `filectime` |
| `safename(path)` | returns a name that is safe to use as a filename |

### Strings

| name      | description |
| --------- | ----------- |
| `cat(args...)` | Concatenate all arguments together as one string |
| `sprintf(pattern, args...)` | PHP's `sprintf` |
| `str(args...)`     | Resolve the expression to a string variable (as if it was outputted) |
| `escape`  | Escape the passed value for use in the shell |
| `join`    | PHP's `join` | 
| `str_replace` | PHP's `str_replace` |
| `range(end, start=1)`   | Returns a range of numbers, from `start` to `end` |
| `url.host(url)` | Returns the host name extracted from the passed url |

### Other

| name      | description |
| --------- | ----------- |
| `keys(object)`    | Return all keys of the passed object |

## Environment variables 

The following environment variables are used in Z:

### `ZFILE` (default: `z.yml`)
What filename to look for when running Z. 

### `ZPLUGINPATH` (default: empty)
Where to look for plugins. You can use colon-separated list of paths to look,
and the path may contain globbing patterns. A typical useful value would be
something like: `$HOME/z-plugins/vendor/zicht/z-plugin-*`

### `ZPATH` (default: `$PWD:$HOME/.config/z`)
Where to look for the `ZFILE`. Usually the most practical approach is to 
have a `z.yml` per project, but you could also decide to have one globally
for your own custom tooling.
