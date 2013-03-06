<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Container;

use \Zicht\Tool\Script;
use \Zicht\Tool\PluginInterface;
use \UnexpectedValueException;
use \Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\Process\Process;
use Zicht\Tool\Script\Compiler as ScriptCompiler;
use Zicht\Tool\Util;

/**
 * Service container
 */
class Container
{
    const SHELL = '/bin/bash';

    protected $plugins = array();
    protected $subscribers = array();

    public $output;

    public $definition = '';

    protected $values = array();
    protected $declarations = array();
    protected $functions = array();

    protected $stack = array();
    protected $prefix = array();

    /**
     * Construct the container with the specified values as services/values.
     *
     * @param array $values
     */
    public function __construct()
    {
        $this->values = array();

        $this->subscribe(array($this, 'prefixListener'));

        $this->values += array(
            'explain'       => false,
            'verbose'       => false,
            'interactive'   => false,
            'force'         => false
        );
        // gather the options for nested z calls.
        $opts = array();
        foreach (array('force', 'verbose', 'explain') as $opt) {
            if ($this->values[$opt]) {
                $opts[]= '--' . $opt;
            }
        }
        $this->set(array('z', 'opts'), join(' ', $opts));

        $this->fn('sprintf');
        $this->fn('cat', function() {
            return join('', func_get_args());
        });
    }


    public function get($path)
    {
        return $this->lookup($this->values, $path);
    }


    public function lookup($ptr, array $path) {
        $at = array();
        while ($sub = array_shift($path)) {
            if (is_object($ptr)) {
                $ptr = $ptr->$sub;
            } elseif (is_array($ptr)) {
                if (!array_key_exists($sub, $ptr)) {
                    throw new \InvalidArgumentException("Member not found: " . join('.', $at) . ".$sub, available keys are: " . join(', ', array_keys($ptr)));
                }
                $ptr = $ptr[$sub];
            } else {
                throw new \InvalidArgumentException("Item at path " . join('.', $at) . ".$sub, should either be object or array, but " . gettype($ptr) . ' found');
            }
            $at[]= $sub;
        }
        return $ptr;
    }


    public function resolve($id)
    {
        if (is_string($id)) {
            if (strpos($id, '.') !== false) {
                trigger_error("As of version 1.1, Resolving variables by string is deprecated ($id). Please use arrays in stead", E_USER_DEPRECATED);
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
        } catch(\Exception $e) {
            throw new \RuntimeException("Exception while resolving value " . join("->", $id), 0, $e);
        }
    }


    public function set($path, $value)
    {
        if (!is_array($path)) {
            if (strpos($path, '.') !== false) {
                trigger_error(
                    "As of version 1.1, setting variables by string is deprecated ($path)."
                    . "Please use arrays instead",
                    E_USER_DEPRECATED
                );
                $path = explode('.', $path);
            } else {
                $path = array($path);
            }
        }
        $strPath = join('->', $path);
        $ptr =& $this->values;
        $last = array_pop($path);
        foreach ($path as $key) {
            if (is_array($ptr)) {
                if (!isset($ptr[$key])) {
                    $ptr[$key] = array();
                }
                $ptr =& $ptr[$key];
            } else {
                if (!isset($ptr->$key)) {
                    $ptr->$key = array();
                }
                $ptr =& $ptr->$key;
            }
        }
        if (is_array($ptr)) {
            $ptr[$last] = $value;
        } elseif (is_object($ptr) && ! $ptr instanceof \Closure) {
            $ptr->$last = $value;
        } else {
            throw new UnexpectedValueException("Unexpected type " . (is_object($ptr) ? get_class($ptr) : gettype($ptr)) . " {$strPath}");
        }
    }


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


    public function method($id, $callable)
    {
        $this->fn($id, $callable, true);
    }


    public function decl($id, $callable)
    {
        if (!is_callable($callable)) {
            throw new \InvalidArgumentException("Not callable");
        }
        $this->set($id, $callable);
    }



    public function notice($message)
    {
        $this->output->writeln("# <comment>NOTICE: $message</comment>");
    }


    public function evaluate($script)
    {
        $exprcompiler  = new ScriptCompiler(
            new \Zicht\Tool\Script\Parser\Expression(),
            new \Zicht\Tool\Script\Tokenizer\Expression()
        );

        $z = $this;
        $_value = null;
        eval('$_value = ' . $exprcompiler->compile($script) . ';');
        return $_value;
    }


    public function has($id)
    {
        if (is_string($id)) {
            $id = array($id);
        }
        try {
            $this->lookup($this->values, $id);
        } catch(\InvalidArgumentException $e) {
            return false;
        }
        return true;
    }


    /**
     * Separate helper for calling a service as a function.
     *
     * @return mixed
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


    function prefixListener($task, $event)
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
     * @return int
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
                $process = new Process(self::SHELL);
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
     */
    public function value($value)
    {
        if (is_array($value)) {
            if (! array_reduce(array_map('is_scalar', $value), function($a, $b) { return is_scalar($a) && $b; }, true)) {
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
}