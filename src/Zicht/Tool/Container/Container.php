<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Container;

use Zicht\Tool\Script;

class Container extends \Pimple {
    function __construct(array $values = array()) {
        parent::__construct($values);

        $this['now'] = date('YmdHis');
        $this['date'] = date('Ymd');
    }


    function exec($script) {
        $cmd = $this->evaluate($script);
        if (isset($this['executor'])) {
            $ret = call_user_func($this['executor'], $cmd);
        } else {
            $ret = null;
            passthru($cmd, $ret);
        }

        if ($ret != 0) {
            throw new \UnexpectedValueException("Command '$cmd' failed with exit code {$ret}");
        }
    }


    public function evaluate($script)
    {
        $parser = new Script($script);
        $cmd = $parser->evaluate($this);
        return $cmd;
    }


    function cmd($cmd) {
        if (substr($cmd, 0, 1) === '@') {
            return $this['tasks.' . substr($cmd, 1)];
        }
        return $this->exec($cmd);
    }


    function select($namespace, $key) {
        $this[$namespace] = $key;
        if (!isset($this['__config'][$namespace][$key])) {
            throw new \InvalidArgumentException("Invalid {$namespace} provided, {$namespace}.{$key} is not defined");
        }
        foreach ($this['__config'][$namespace][$key] as $name => $value) {
            $this[$namespace . '.' . $name] = $value;
        }
    }
}