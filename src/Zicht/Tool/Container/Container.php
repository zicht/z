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

/**
 * Service container
 */
class Container
{
    protected $plugins = array();
    protected $subscribers = array();

    public $output;

    // TODO check if config really needs to be public
    public $config;

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
    public function __construct($vars, $config)
    {
        $this->values = $vars;
        $this->config = $config;

        $this->subscribe(array($this, 'prefixListener'));

        if (!$this->has('explain')) {
            $this->set('explain', false);
        }
        if (!$this->has('verbose')) {
            $this->set('verbose', false);
        }
        if (!$this->has('force')) {
            $this->set('force', false);
        }
        $this->set('interactive', false);
    }

    public function resolve($id)
    {
        if (in_array($id, $this->stack)) {
            throw new \UnexpectedValueException("Circular reference detected: " . implode(' -> ', $this->stack) . ' -> ' . $id);
        }

        array_push($this->stack, $id);
        if (!array_key_exists($id, $this->values)) {
            if (array_key_exists($id, $this->declarations)) {
                $this->values[$id] = call_user_func($this->declarations[$id], $this);
            } else {
                var_dump(array_keys($this->declarations));
                throw new \InvalidArgumentException("Unresolvable value [$id]");
            }
        }
        array_pop($this->stack);

        return $this->values[$id];
    }


    public function set($id, $value)
    {
        $this->values[$id] = $value;
    }


    public function fn($id, $callable = null, $needsContainer = false)
    {
        if ($callable === null) {
            $callable = $id;
        }
        if (!is_callable($callable)) {
            throw new \InvalidArgumentException("Not callable");
        }
        $this->functions[$id] = array($callable, $needsContainer);
    }


    public function method($id, $callable) {
        $this->fn($id, $callable, true);
    }


    public function decl($id, $callable)
    {
        if (!is_callable($callable)) {
            throw new \InvalidArgumentException("Not callable");
        }
        $this->declarations[$id] = $callable;
    }


    public function has($id)
    {
        return !empty($this->values[$id]);
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
        $service = $this->functions[$service];
        if (!is_callable($service[0])) {
            throw new \InvalidArgumentException("Can not use service '$service' as a function, it is not callable");
        }
        if ($service[1]) {
            array_unshift($args, $this);
        }
        return call_user_func_array($service[0], $args);
    }


    function prefixListener($task, $event)
    {
        if (!$this->resolve('explain') && !$this->resolve('verbose')) {
            return;
        }
        $reset = false;
        switch ($event) {
            case 'start':
                $this->prefix[]= (string)$task;
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
            if ($this->resolve('explain')) {
                $this->output->writeln('( ' . rtrim($cmd, "\n") . ' );');
            } elseif ($this->resolve('interactive')) {
                passthru($cmd, $ret);
            } else {
                $process = new \Symfony\Component\Process\Process($cmd);
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
     * Evaluate a script and return the value
     *
     * @param string $script
     * @return string
     */
    public function evaluate($script)
    {
        $parser = new Script($script);
        $cmd = $parser->evaluate($this);
        return $cmd;
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
        if (substr($cmd, 0, 1) === '@') {
            return $this->resolve('tasks.' . substr($cmd, 1));
        }
        return $this->exec($cmd);
    }


    /**
     * Select a nested configuration for aliased use, typically for 'env'
     *
     * @param string $namespace
     * @param string $key
     * @return void
     */
    public function select($namespace, $key)
    {
        $this->values[$namespace] = $key;
        if (!isset($this->config[$namespace][$key])) {
            throw new \InvalidArgumentException("Invalid {$namespace} provided, {$namespace}.{$key} is not defined");
        }
        foreach ($this->config[$namespace][$key] as $name => $value) {
            $this->values[$namespace . '.' . $name] = $value;
        }
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
     * Notifies registered subscribers of an event
     *
     * @param string $task
     * @param string $event
     * @return void
     */
    public function notify($task, $event)
    {
        foreach ($this->subscribers as $subscriber) {
            call_user_func($subscriber, $task, $event);
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