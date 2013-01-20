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

/**
 * Service container
 */
class Container extends Pimple
{
    protected $plugins = array();
    protected $subscribers = array();

    /**
     * Construct the container with the specified values as services/values.
     *
     * @param array $values
     */
    public function __construct(array $values = array())
    {
        parent::__construct($values);
        $container = $this;

        $this['executor'] = $this->protect(
            function($cmd, $interact = false) use($container) {
                if (trim($cmd)) {
                    $ret = null;
                    if ($interact) {
                        passthru($cmd, $ret);
                    } else {
                        $process = new \Symfony\Component\Process\Process($cmd);
                        $process->run(function($mode, $data) use($container) {
                            call_user_func($container['std' . $mode], $data);
                        });
                        $ret = $process->getExitCode();
                    }
                    return $ret;
                }
                return null;
            }
        );
        $this['force'] = false;
        $this['verbose'] = false;

        $this['stdout'] = $this->protect(function($data) use($container){
            $container['console_output']->write($data);
        });
        $this['stderr'] = $this->protect(function($data) use($container){
            $container['console_output']->write($data);
        });
        $this['expand']= $this->protect(function($value) {
            if (is_callable($value)) {
                $value = call_user_func($value);
            }
            if (is_array($value)) {
                $value = join(' ', $value);
            }
            return (string) $value;
        });
    }



    public function decorate($service, $decorator)
    {
        $this[$service] = $this->protect($decorator($this[$service]));
    }

    /**
     * Executes a script snippet using the 'executor' service.
     *
     * @param string $script
     * @return int
     */
    public function exec($script)
    {
        $ret = call_user_func($this['executor'], $script);

        if ($ret != 0) {
            throw new \UnexpectedValueException("Command '$script' failed with exit code {$ret}");
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