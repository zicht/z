<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */
namespace Zicht\Tool\Container;


class Task implements Compilable
{
    function __construct($taskDef) {
        $this->taskDef = $taskDef;
    }


    function compile(Compiler $compiler, $indent = 1)
    {
        $indentStr = str_repeat('    ', $indent);

        $ret = '$' . $compiler->getContainerName() . '->share(function($z) {' . PHP_EOL . $indentStr;

        foreach ($this->taskDef['set'] as $name => $value) {
            $ret .= '$z[' . var_export($name, true) . '] = $z->evaluate(' . var_export($value, true) . ');' . PHP_EOL . $indentStr;
        }

        foreach (array('pre', 'do', 'post') as $scope) {
            foreach ($this->taskDef[$scope] as $cmd) {
                $ret .= '$z->cmd(' . var_export($cmd, true) . ');' . PHP_EOL . $indentStr;
            }
        }
        if (isset($this->taskDef['yield'])) {
            $ret .= 'return $z[' . var_export($this->taskDef['yield'], true) . '];';
        }

        $ret .= PHP_EOL . '})';
        return $ret;
    }
}
