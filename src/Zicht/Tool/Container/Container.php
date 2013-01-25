<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Container;

use \Zicht\Tool\Script;
use \Zicht\Tool\PluginInterface;
use \UnexpectedValueException;
use \Pimple;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Service container
 */
class Container extends Pimple
{
    protected $plugins = array();
    protected $subscribers = array();

    public $output;

    protected $stack = array();
    protected $prefix = array();

    /**
     * Construct the container with the specified values as services/values.
     *
     * @param array $values
     */
    public function __construct($verbose, $force, $explain)
    {
        parent::__construct(array(
            'verbose'     => (bool)$verbose,
            'force'       => (bool)$force,
            'explain'     => (bool)$explain,
            'interactive' => false
        ));

        if (!$explain || $verbose) {
            $this->subscribe(array($this, 'prefixListener'));
        }
    }

    public function offsetGet($id)
    {
        if (in_array($id, $this->stack)) {
            throw new \UnexpectedValueException("Circular reference detected: " . implode(' -> ', $this->stack) . ' -> ' . $id);
        }
        array_push($this->stack, $id);
        $ret = parent::offsetGet($id);
        array_pop($this->stack);
        return $ret;
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
        $service = $this->offsetGet($service);
        if (!is_callable($service)) {
            throw new \InvalidArgumentException("Can not use service '$service' as a function, it is not callable");
        }
        return call_user_func_array($service, $args);
    }


    function prefixListener($task, $event)
    {
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
            if ($this->raw('explain')) {
                $this->output->writeln('( ' . rtrim($cmd, "\n") . ' );');
            } elseif ($this->raw('interactive')) {
                passthru($cmd, $ret);
            } else {
                $ret = null;
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
        $this->output->write($data);
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
            return $this['tasks.' . substr($cmd, 1)];
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
        $this[$namespace] = $key;
        if (!isset($this['__config'][$namespace][$key])) {
            throw new \InvalidArgumentException("Invalid {$namespace} provided, {$namespace}.{$key} is not defined");
        }
        foreach ($this['__config'][$namespace][$key] as $name => $value) {
            $this[$namespace . '.' . $name] = $value;
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
     * Register a list of plugins with the container
     *
     * @param PluginInterface[] $plugins
     * @return void
     */
    public function setPlugins(array $plugins)
    {
        foreach ($plugins as $plugin) {
            $this->addPlugin($plugin);
        }
    }


    /**
     * Add a single plugin to the container and register the container in the plugin using setContainer
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