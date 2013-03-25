<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Container;

use \Symfony\Component\Process\Process;
use \Symfony\Component\PropertyAccess\PropertyAccess;
use \Zicht\Tool\Script;
use \Zicht\Tool\PluginInterface;
use \UnexpectedValueException;
use \Zicht\Tool\Script\Compiler as ScriptCompiler;
use \Zicht\Tool\Util;
use \Zicht\Tool\Script\Parser\Expression as ExpressionParser;
use \Zicht\Tool\Script\Tokenizer\Expression as ExpressionTokenizer;

/**
 * Service container
 */
class Container
{
    /**
     * The default shell used for scripts.
     */
    public static $SHELL = '/bin/bash';

    protected $plugins = array();
    protected $subscribers = array();
    protected $values = array();
    protected $prefix = array();

    private $stack = array();

    public $output;

    /**
     * Construct the container with the specified values as services/values.
     */
    public function __construct()
    {
        $this->subscribe(array($this, 'prefixListener'));

        $this->values = array(
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


        $this->fn('sprintf');
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
     * @return mixed
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function lookup($context, $path)
    {
        if (empty($path)) {
            throw new \InvalidArgumentException("Passed lookup path is empty.");
        }
        try {
            return PropertyAccess::getPropertyAccessor()->getValue(
                $context,
                new Context\ArrayPropertyPath($this->path($path))
            );
        } catch (\Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException $e) {
            throw new \RuntimeException("Error resolving " . join(".", $path), 0, $e);
        } catch (\Symfony\Component\PropertyAccess\Exception\OutOfBoundsException $e) {
            throw new \RuntimeException("Error resolving " . join(".", $path), 0, $e);
        }
    }

    /**
     * Resolve the specified path. If the resulting value is a Closure, it's assumed a declaration.
     *
     * @param array $id
     * @return mixed
     *
     * @throws \RuntimeException
     * @throws \UnexpectedValueException
     */
    public function resolve($id)
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
            if (in_array($id, $this->stack)) {
                throw new \UnexpectedValueException(
                    sprintf(
                        "Circular reference detected: %s->%s",
                        implode(' -> ', $this->stack),
                        $id
                    )
                );
            }
            array_push($this->stack, $id);
            if (!is_array($id)) {
                $id = array($id);
            }
            $ret = $this->lookup($this->values, $id);

            if ($ret instanceof \Closure) {
                $this->set($id, $ret = call_user_func($ret, $this));
            }
            array_pop($this->stack);
            return $ret;
        } catch (\Exception $e) {
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
        PropertyAccess::getPropertyAccessor()->setValue(
            $this->values,
            new Context\ArrayPropertyPath($this->path($path)),
            $value
        );
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
        $this->set($id, $callable);
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
     *
     * @param string $expression
     * @return mixed
     */
    public function evaluate($expression)
    {
        $exprcompiler  = new ScriptCompiler(new ExpressionParser(), new ExpressionTokenizer());

        $z = $this;
        $_value = null;
        eval('$_value = ' . $exprcompiler->compile($expression) . ';');
        return $_value;
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
            throw new \InvalidArgumentException("Can not use service '$service' as a function, it is not callable");
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
            if ($this->output->getVerbosity() > 1) {
                $this->output->setPrefix('<info>[' . join('][', $this->prefix) . ']</info> ');
            } elseif (count($this->prefix) > 1) {
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
     * @return integer
     * @return int
     *
     * @throws \UnexpectedValueException
     */
    public function exec($cmd)
    {
        $ret = 0;
        if (trim($cmd)) {
            if ($this->resolve(array('explain'))) {
                $this->output->writeln('( ' . rtrim($cmd, "\n") . ' );');
            } elseif ($this->resolve(array('interactive'))) {
                passthru($cmd, $ret);
            } else {
                $process = new Process(self::$SHELL);
                $process->setStdin($cmd);
                $process->run(array($this, 'processCallback'));
                $ret = $process->getExitCode();
            }
        }
        if ($ret != 0) {
            throw new \UnexpectedValueException("Command '$cmd' failed with exit code {$ret}");
        }

        return $ret;
    }


    /**
     * The callback used for the process executed by exec()
     *
     * @param mixed $mode
     * @param string $data
     * @return void
     */
    public function processCallback($mode, $data)
    {
        if (isset($this->output)) {
            $this->output->write($data);
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
            return $this->resolve(array_merge(array('tasks'), explode('.', substr($cmd, 1))));
        }
        return $this->exec($cmd);
    }


    /**
     * Returns the value representation of the requested variable
     *
     * @param string $value
     * @return string
     *
     * @throws \UnexpectedValueException
     */
    public function value($value)
    {
        if (is_array($value)) {
            $allScalar = function ($a, $b) {
                return is_scalar($a) && $b;
            };
            if (! array_reduce($value, $allScalar, true)) {
                throw new UnexpectedValueException("Unexpected complex type " . Util::toPhp($value));
            }
            return join(' ', $value);
        }
        if ($value instanceof \Closure) {
            $value = call_user_func($value, $this);
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



    public function addCommand(\Symfony\Component\Console\Command\Command $command)
    {
        $this->commands[]= $command;
    }


    public function getCommands()
    {
        return $this->commands;
    }
}