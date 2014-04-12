<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Container;

use \Symfony\Component\Console\Command\Command;
use \Zicht\Tool\PropertyPath\PropertyAccessor;
use \Symfony\Component\Console\Output\NullOutput;
use \Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\Process\Process;
use \Zicht\Tool\Script;
use \Zicht\Tool\PluginInterface;
use \Zicht\Tool\Script\Compiler as ScriptCompiler;
use \Zicht\Tool\Util;
use \Zicht\Tool\Script\Parser\Expression as ExpressionParser;
use \Zicht\Tool\Script\Tokenizer\Expression as ExpressionTokenizer;

use \UnexpectedValueException;

/**
 * Service container
 */
class Container
{
    /**
     * Exit code used by commands to identify that they should abort the entire script
     */
    const ABORT_EXIT_CODE = 42;

    protected $plugins = array();
    protected $subscribers = array();
    protected $commands = array();
    protected $values = array();
    protected $prefix = array();

    private $resolutionStack = array();
    private $varStack = array();

    public $output;
    public $executor;

    /**
     * Construct the container with the specified values as services/values.
     *
     * @param Executor $executor
     * @param OutputInterface $output
     */
    public function __construct(Executor $executor = null, $output = null)
    {
        $this->executor = $executor ?: new Executor($this);
        $this->output = $output ?: new NullOutput();


        $this->subscribe(array($this, 'prefixListener'));

        $this->values = array(
            'SHELL'         => '/bin/bash',
            'TIMEOUT'       => 300,
            'interactive'   => false,
        );
        // gather the options for nested z calls.
        $this->set(
            array('z', 'opts'),
            function($z) {
                $opts = array();
                foreach (array('force', 'verbose', 'explain') as $opt) {
                    if ($z->has($opt) && $z->get($opt)) {
                        $opts[]= '--' . $opt;
                    }
                }
                return join(' ', $opts);
            }
        );
        $this->set(
            array('z', 'cmd'),
            $_SERVER['argv'][0]
        );

        $this->fn('sprintf');
        $this->fn('is_file');
        $this->fn('is_dir');
        $this->fn('confirm', function() {
            return false;
        });
        $this->fn('keys', 'array_keys');
        $this->fn('mtime', 'filemtime');
        $this->fn('atime', 'fileatime');
        $this->fn('ctime', 'filectime');
        $this->fn('sh', array($this, 'helperExec'));
        $this->fn('str', array($this, 'str'));
        $this->fn(
            array('url', 'host'),
            function($url) {
                return parse_url($url, PHP_URL_HOST);
            }
        );
        $this->decl(array('now'), function() {
            return date('YmdHis');
        });
        $this->fn(array('safename'), function($fn) {
            return preg_replace('/[^a-z0-9]+/', '-', $fn);
        });
        $this->decl('abort', function() {
            return 'exit ' . self::ABORT_EXIT_CODE;
        });
        $this->fn(
            'cat',
            function() {
                return join('', func_get_args());
            }
        );
        $this->set('cwd',  getcwd());
        $this->set('user', getenv('USER'));
    }


    /**
     * Return the raw context value at the specified path.
     *
     * @param array $path
     * @return mixed
     */
    public function get($path)
    {
        return $this->lookup($this->values, $path);
    }


    /**
     * Looks up a path in the specified context
     *
     * @param array $context
     * @param array $path
     * @param bool $require
     * @return mixed
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function lookup($context, $path, $require = false)
    {
        if (empty($path)) {
            throw new \InvalidArgumentException("Passed lookup path is empty.");
        }
        if (empty($context)) {
            var_dump($context);
            xdebug_print_function_stack();
            throw new \InvalidArgumentException("Context is empty");
        }
        return PropertyAccessor::getByPath($context, $this->path($path), $require);
    }

    /**
     * Resolve the specified path. If the resulting value is a Closure, it's assumed a declaration.
     *
     * @param array $id
     * @param bool $required
     * @return mixed
     *
     * @throws \RuntimeException
     * @throws CircularReferenceException
     */
    public function resolve($id, $required = false)
    {
        if (is_string($id)) {
            if (strpos($id, '.') !== false) {
                trigger_error(
                    "As of version 1.1, Resolving variables by strings containing dots is deprecated ($id). "
                    . "Please use arrays in stead",
                    E_USER_DEPRECATED
                );
            }
            $id = explode('.', $id);
        }
        try {
            if (in_array($id, $this->resolutionStack)) {
                $path = array_map(
                    function($a) {
                        return join('.', $a);
                    },
                    $this->resolutionStack
                );

                throw new CircularReferenceException(
                    sprintf(
                        "Circular reference detected: %s -> %s",
                        implode(' -> ', $path),
                        join('.', $id)
                    )
                );
            }
            array_push($this->resolutionStack, $id);
            $ret = $this->lookup($this->values, $id, $required);
            if ($ret instanceof \Closure) {
                $this->set($id, $ret = call_user_func($ret, $this));
            }
            array_pop($this->resolutionStack);
            return $ret;
        } catch (\Exception $e) {
            if ($e instanceof CircularReferenceException) {
                throw $e;
            }
            throw new \RuntimeException("Exception while resolving value " . join(".", $id), 0, $e);
        }
    }


    /**
     * Set the value at the specified path
     *
     * @param array $path
     * @param mixed $value
     * @return void
     *
     * @throws \UnexpectedValueException
     */
    public function set($path, $value)
    {
        PropertyAccessor::setByPath($this->values, $this->path($path), $value);
    }

    /**
     * This is useful for commands that need the shell regardless of the 'explain' value setting.
     *
     * @param string $cmd
     * @return mixed
     */
    public function helperExec($cmd)
    {
        if ($this->resolve('explain')) {
            $this->output->writeln("# Task needs the following helper command:");
            $this->output->writeln("# " . str_replace("\n", "\\n", $cmd));
        }
        $ret = '';
        $this->executor->execute($cmd, $ret);
        return $ret;
    }

    /**
     * Wrapper for converting string paths to arrays.
     *
     * @param mixed $path
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    private function path($path)
    {
        if (!is_array($path)) {
            if (null === $path) {
                throw new \InvalidArgumentException("Invalid path");
            } elseif (strpos($path, '.') !== false) {
                trigger_error(
                    "As of version 1.1, setting variable paths by string is deprecated ($path)."
                        . "Please use arrays instead",
                    E_USER_DEPRECATED
                );
                $path = explode('.', $path);
            } else {
                $path = array($path);
            }
        }
        return $path;
    }


    /**
     * Set a function at the specified path.
     *
     * @param array $id
     * @param callable $callable
     * @param bool $needsContainer
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function fn($id, $callable = null, $needsContainer = false)
    {
        if ($callable === null) {
            $callable = $id;
        }
        if (!is_callable($callable)) {
            throw new \InvalidArgumentException("Not callable");
        }
        $this->set($id, array($callable, $needsContainer));
    }


    /**
     * Creates a method-type function, i.e. the first parameter will always be the container.
     *
     * @param array $id
     * @param callable $callable
     * @return void
     */
    public function method($id, $callable)
    {
        $this->fn($id, $callable, true);
    }


    /**
     * Does a declaration, i.e., the first time the declaration is called, it's resulting value overwrites the
     * declaration.
     *
     * @param array $id
     * @param callable $callable
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function decl($id, $callable)
    {
        if (!is_callable($callable)) {
            throw new \InvalidArgumentException("Not callable");
        }
        $this->set($id, function(Container $c) use($callable, $id) {
            $value = call_user_func($callable, $c);
            $c->set($id, $value);
            return $value;
        });
    }

    /**
     * Output a notice
     *
     * @param string $message
     * @return void
     */
    public function notice($message)
    {
        $this->output->writeln("# <comment>NOTICE: $message</comment>");
    }


    /**
     * Does an on-the-fly evaluation of the specified expression.
     * The compilation result will be stored in $code.
     *
     * @param string $expression
     * @param string &$code
     *
     * @return mixed
     */
    public function evaluate($expression, &$code = null)
    {
        $exprcompiler  = new ScriptCompiler(new ExpressionParser(), new ExpressionTokenizer());

        $z = clone $this;
        $_value = null;
        $code = '$z->set(\'_\', ' . $exprcompiler->compile($expression) . ');';
        eval($code);

        return $z->resolve('_');
    }


    /**
     * Checks for existence of the specified path.
     *
     * @param string $id
     * @return string
     */
    public function has($id)
    {
        try {
            $existing = $this->get($id);
        } catch (\OutOfBoundsException $e) {
            return false;
        }
        return Util::typeOf($existing);
    }


    /**
     * Checks if a value is empty.
     *
     * @param mixed $path
     * @return bool
     */
    public function isEmpty($path)
    {
        try {
            $value = $this->get($path);
        } catch (\OutOfBoundsException $e) {
            return false;
        }
        return empty($value);
    }


    /**
     * Separate helper for calling a service as a function.
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public function call()
    {
        $args = func_get_args();
        $service = array_shift($args);
        if (!is_callable($service[0])) {
            throw new \InvalidArgumentException("Can not use service '{$service[0]}' as a function, it is not callable");
        }

        // if the service needs the container, it is specified in the decl() call as the second param:
        if ($service[1]) {
            array_unshift($args, $this);
        }
        return call_user_func_array($service[0], $args);
    }


    /**
     * The prefix listener makes sure the output gets the right prefix at the right time.
     *
     * TODO this needs to be removed from the container and put somewhere else.
     *
     * @param array $task
     * @param string $event
     * @return void
     */
    public function prefixListener($task, $event)
    {
        if ($this->resolve(array('explain')) && !$this->resolve(array('verbose'))) {
            // don't do prefixing if the "explain" option is given, unless the "verbose" option is given too
            // in the latter case we do want the prefixes (because then we would want to know why certain
            // pieces are executed
            return;
        }
        $reset = false;
        switch ($event) {
            case 'start':
                $this->prefix[]= join(':', $task);
                $reset = true;
                break;
            case 'end':
                $this->prefix = array_slice($this->prefix, 0, -1);
                $reset = true;
                break;
        }

        if ($reset) {
            if ($this->output->getVerbosity() > 2) {
                $this->output->setPrefix('<info>[' . join('][', $this->prefix) . ']</info> ');
            } elseif ($this->output->getVerbosity() > 1 && count($this->prefix) > 1) {
                $prefix = $this->prefix[count($this->prefix) -1];
                if (strlen($prefix) > 21) {
                    $prefix = substr($prefix, 0, 9) . '...' . substr($prefix, -9);
                }
                $prefix = str_pad($prefix, 21, ' ', STR_PAD_LEFT);
                $this->output->setPrefix('<info>[' . $prefix . ']</info> ');
            } else {
                $this->output->setPrefix('');
            }
        }
    }

    /**
     * Executes a script snippet using the 'executor' service.
     *
     * @param string $cmd
     * @return void
     */
    public function exec($cmd)
    {
        if (trim($cmd)) {
            if ($this->resolve('explain')) {
                $this->output->writeln('echo ' . escapeshellarg(trim($cmd)) . ' | ' . $this->resolve(array('SHELL')));
            } else {
                $this->executor->execute($cmd);
            }
        }
    }

    /**
     * Execute a command. This is a wrapper for 'exec', so that a task prefixed with '@' can be passed as well.
     *
     * @param string $cmd
     * @return int
     * @return int
     */
    public function cmd($cmd)
    {
        $cmd = ltrim($cmd);
        if (substr($cmd, 0, 1) === '@') {
            return $this->resolve(array_merge(array('tasks'), explode('.', substr($cmd, 1))), true);
        }
        return $this->exec($cmd);
    }


    /**
     * Returns the value representation of the requested variable
     *
     * @param string $value
     * @return string
     *
     * @deprecated
     * @throws \UnexpectedValueException
     */
    public function value($value)
    {
        if ($value instanceof \Closure) {
            $value = call_user_func($value, $this);
        }
        return $value;
    }


    /**
     * Convert the value to a string.
     *
     * @param mixed $value
     * @return string
     * @throws \UnexpectedValueException
     */
    public function str($value)
    {
        if (is_array($value)) {
            $allScalar = function ($a, $b) {
                return $a && is_scalar($b);
            };
            if (! array_reduce($value, $allScalar, true)) {
                throw new UnexpectedValueException("Unexpected complex type " . Util::toPhp($value));
            }
            return join(' ', $value);
        }
        return (string)$value;
    }


    /**
     * Returns all registered plugins
     *
     * @return PluginInterface[]
     */
    public function getPlugins()
    {
        return $this->plugins;
    }

    /**
     * Register a plugin
     *
     * @param \Zicht\Tool\PluginInterface $plugin
     * @return void
     */
    public function addPlugin(PluginInterface $plugin)
    {
        $this->plugins[]= $plugin;
        $plugin->setContainer($this);
    }


    /**
     * Notifies registered subscribers of an event
     *
     * @param string $task
     * @param string $event
     * @return void
     */
    public function notify($task, $event)
    {
        foreach ($this->subscribers as $subscriber) {
            call_user_func($subscriber, $task, $event, $this);
        }
    }


    /**
     * Subscribe to the events dispatched by notify()
     *
     * @param callable $callback
     * @return void
     */
    public function subscribe($callback)
    {
        $this->subscribers[]= $callback;
    }


    /**
     * Register a command
     *
     * @param \Symfony\Component\Console\Command\Command $command
     * @return void
     */
    public function addCommand(Command $command)
    {
        $this->commands[]= $command;
    }


    /**
     * Returns the registered commands
     *
     * @return Task[]
     */
    public function getCommands()
    {
        return $this->commands;
    }

    /**
     * Returns the values.
     *
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }


    /**
     * Clones all plugins and resets them to their initial state
     *
     * @return void
     */
    public function __clone()
    {
        $plugins = $this->plugins;
        $this->plugins = array();
        foreach ($plugins as $p) {
            $this->addPlugin(clone $p);
        }
    }


    /**
     * Check whether we're in debug mode or not.
     *
     * @return bool
     */
    public function isDebug()
    {
        return $this->resolve('debug') === true;
    }


    /**
     * Push a var on a local stack by it's name.
     * 
     * @param string $varName
     * @param string $tail
     * @return void
     */
    public function push($varName, $tail)
    {
        if (!isset($this->varStack[$varName])) {
            $this->varStack[$varName] = array();
        }
        array_push($this->varStack[$varName], $this->get($varName));
        $this->set($varName, $tail);
    }


    /**
     * Pop a var from a local var stack.
     *
     * @param string $varName
     * @return void
     */
    public function pop($varName)
    {
        $this->set($varName, array_pop($this->varStack[$varName]));
    }
}
