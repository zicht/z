<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Container;

/**
 * Compilable node that represents an executable task
 */
class Task implements Compilable
{
    /**
     * Construct the task with the provided array as task definition
     *
     * @param array $taskDef
     */
    public function __construct(array $taskDef)
    {
        $this->taskDef = $taskDef;
    }


    /**
     * Compile the node
     *
     * @param Compiler $compiler
     * @param int $indent
     * @return string
     */
    public function compile(Compiler $compiler, $indent = 1)
    {
        $indentStr = str_repeat('    ', $indent);

        $ret = '$' . $compiler->getContainerName() . '->share(function($z) {' . PHP_EOL . $indentStr;

        foreach ($this->taskDef['set'] as $name => $value) {
            if ($value && preg_match('/^\?\s*(.*)/', trim($value), $m)) {
                $ret .= 'if (!isset($z[' . var_export($name, true) . '])) {';
                if (!$m[1]) {
                    $ret .= sprintf(
                        'throw new \RuntimeException(\'required variable %s is not defined\');',
                        $name,
                        true
                    );
                } else {
                    $ret .= sprintf(
                        '$z[%s] = $z->evaluate(%s);' . PHP_EOL . $indentStr,
                        var_export($name, true),
                        var_export($m[1], true)
                    );
                }
                $ret .= '}';
            } else {
                $ret .= sprintf(
                    '$z[%s] = $z->evaluate(%s);' . PHP_EOL . $indentStr,
                    var_export($name, true),
                    var_export($value, true)
                );
            }
        }
        if (!empty($this->taskDef['unless'])) {
//            $ret .= 'if (!' . $compiler->expr($this->taskDef['unless']) . ') {';
        }
        foreach (array('pre', 'do', 'post') as $scope) {
            foreach ($this->taskDef[$scope] as $cmd) {
                $ret .= '$z->cmd(' . var_export($cmd, true) . ');' . PHP_EOL . $indentStr;
            }
        }
        if (!empty($this->taskDef['unless'])) {
            $ret .= '}';
        }

        if (isset($this->taskDef['yield'])) {
            $ret .= 'return $z[' . var_export($this->taskDef['yield'], true) . '];';
        }

        $ret .= PHP_EOL . '})';
        return $ret;
    }


    /**
     * Returns all variables that can be injected into the task.
     *
     * @return array
     */
    public function getVariables($onlyPublic = true)
    {
        $ret = array();
        if (isset($this->taskDef['set'])) {
            foreach ($this->taskDef['set'] as $name => $expr) {
                if ($onlyPublic && $name{0} === '_') {
                    // Variables prefixed with an underscore are considered non public
                    continue;
                }
                // TODO remove duplicated pattern match in this method and the 'compile'.
                // possibly make a variable declaration a Compilable node too (or just make a real AST)
                if (preg_match('/^\?\s*(.*)/', $expr, $m)) {
                    // if the part after the question mark is empty, the variable is assumed to be required
                    // for execution of the task
                    $ret[$name] = ($m[1] === '');
                }
            }
        }
        return $ret;
    }
}
