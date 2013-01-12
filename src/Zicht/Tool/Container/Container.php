<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Container;

use \Zicht\Tool\Script;
use \UnexpectedValueException;
use \Pimple;

/**
 * Service container
 */
class Container extends Pimple
{
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
                $ret = null;
                passthru($cmd, $ret);
                return $ret;
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
        $cmd = $this->evaluate($script);
        $ret = call_user_func($this['executor'], $cmd);

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
}