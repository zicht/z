<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Container;

use Zicht\Tool\Script;

class Container extends \Pimple {
    const MODE_EXEC = 'execute';
    const MODE_ECHO = 'echo';


    function __construct() {
        parent::__construct();

        $this['now'] = date('YmdHis');
        $this['date'] = date('Ymd');
    }


    function exec($script) {
        $parser = new Script($script);
        $cmd = $parser->evaluate($this);
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


    function cmd($cmd) {
        if (substr($cmd, 0, 1) === '@') {
            return $this['tasks.' . $cmd];
        }
        return $this->exec($cmd);
    }


    function select($namespace, $key) {
        $this[$namespace] = $key;
        foreach ($this['__config'][$namespace][$key] as $name => $value) {
            $this[$namespace . '.' . $name] = $value;
        }
    }
}