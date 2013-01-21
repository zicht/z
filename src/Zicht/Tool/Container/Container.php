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

    protected $prefix = array();

    /**
     * Construct the container with the specified values as services/values.
     *
     * @param array $values
     */
    public function __construct(OutputInterface $output, $verbose, $force, $explain)
    {
        parent::__construct(array(
            'verbose' => $verbose,
            'force' => $force,
            'explain' => $explain,
            'interactive' => false
        ));
        $this->output = $output;

        if ($verbose) {
            $this->subscribe(array($this, 'prefixListener'));
        }
    }


    function prefixListener($task, $event)
    {
        switch ($event) {
            case 'start':
                array_push($this->prefix, $task);
                $this->output->setPrefix('<info>[' . join('][', $this->prefix) . ']</info> ');
                break;
            case 'end':
                array_pop($this->prefix);
                $this->output->setPrefix('<info>[' . join('][', $this->prefix) . ']</info> ');
                break;
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
            if ($this['explain']) {
                $this->output->writeln('( ' . rtrim($cmd, "\n") . ' );');
            } elseif ($this['interactive']) {
                passthru($cmd, $ret);
            } else {
                $ret = null;
                $process = new \Symfony\Component\Process\Process($cmd);
                $output = $this->output;
                $process->run(function($mode, $data) use($output) {
                    $output->write($data);
                });
                $ret = $process->getExitCode();
            }
        }
        if ($ret != 0) {
            throw new \UnexpectedValueException("Command '$cmd' failed with exit code {$ret}");
        }

        return $ret;
    }


    public function interact($cmd)
    {
        $ret = 0;
        if ($ret != 0) {
            throw new \UnexpectedValueException("Command '$cmd' failed with exit code {$ret}");
        }
        return $ret;
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