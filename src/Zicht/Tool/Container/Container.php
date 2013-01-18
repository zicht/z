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

    /**
     * Construct the container with the specified values as services/values.
     *
     * @param array $values
     */
    public function __construct(array $values = array())
    {
        parent::__construct($values);

        $this['now'] = date('Ymd-H.i.s');
        $this['date'] = date('Ymd');
        $this['cwd'] = getcwd();
        $this['executor'] = $this->protect(
            function($cmd) {
                if (trim($cmd)) {
                    $ret = null;
                    passthru($cmd, $ret);
                    return $ret;
                }
                return null;
            }
        );
        $container = $this;

        $this['ask'] = $this->protect(
            function($q, $default = null) use ($container) {
                return $container['console_dialog_helper']->ask(
                    $container['console_output'],
                    $q . ($default ? sprintf(' [<info>%s</info>]', $default) : '') . ': ',
                    $default
                );
            }
        );
        $this['printf'] = $this->protect(
            function($str) use ($container) {
                $args = func_get_args();
                $tpl = array_shift($args);
                $container['console_output']->write(vsprintf($tpl, $args));
            }
        );
        $this['confirm']= $this->protect(
            function($q, $default = false) use ($container) {
                return $container['console_dialog_helper']->askConfirmation(
                    $container['console_output'],
                    $q .
                    ($default === false ? ' [y/N] ' : ' [Y/n]'),
                    $default
                );
            }
        );
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
}