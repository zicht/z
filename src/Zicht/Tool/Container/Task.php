<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Container;

use Zicht\Tool\Script\Compiler as ScriptCompiler;

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
    public function __construct(array $taskDef, $name)
    {
        $this->taskDef = $taskDef;
        $this->name = $name;
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
        $scriptcompiler = new ScriptCompiler(new \Zicht\Tool\Script\Parser());
        $exprcompiler  = new ScriptCompiler(new \Zicht\Tool\Script\Parser\Expression());

        $indentStr = str_repeat('    ', $indent);

        $ret = '$' . $compiler->getContainerName() . '->share(function($z) {' . PHP_EOL . $indentStr;
        $ret .= sprintf('$z->notify(%s, "start");', var_export($this->name, true));

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
                    $ret .= sprintf('$z[%s] = %s;' . PHP_EOL . $indentStr,
                        var_export($name, true),
                        $scriptcompiler->compile($m[1])
                    );
                }
                $ret .= '}';
            } else {
                $ret .= sprintf(
                    '$z[%s] = %s;' . PHP_EOL . $indentStr,
                    var_export($name, true),
                    $scriptcompiler->compile($value)
                );
            }
        }

        foreach (array('pre', 'do', 'post') as $scope) {
            $ret .= sprintf('$z->notify(%s, %s);', var_export($this->name, true), var_export('before_' . $scope, true));
            $hasUnless = false;
            if ($scope === 'do' && !empty($this->taskDef['unless'])) {
                $ret .= 'if (!$z[\'force\'] && (' . $exprcompiler->compile('$(' . $this->taskDef['unless'] . ')') . ')) {';
                $ret .= '$z[\'stdout\']("<comment>" . ' . var_export($this->taskDef['unless'], true ) . ' . "</comment>, skipped ' . $this->name . '\n");';
                $ret .= '} else {';
                $hasUnless = true;
            }
            foreach ($this->taskDef[$scope] as $cmd) {
                $ret .= sprintf('$z->cmd(%s);', $scriptcompiler->compile($cmd)) . PHP_EOL . $indentStr;
            }
            if ($hasUnless) {
                $ret .= '}';
            }
            $ret .= sprintf('$z->notify(%s, %s);', var_export($this->name, true), var_export('after_' . $scope, true));
        }
        if (isset($this->taskDef['yield'])) {
            $ret .= '$ret = $z[' . var_export($this->taskDef['yield'], true) . '];';
        } else {
            $ret .= '$ret = null;';
        }
        $ret .= sprintf('$z->notify(%s, "end");', var_export($this->name, true));
        $ret .= 'return $ret;';
        $ret .= PHP_EOL . '})';
        return $ret;
    }


    /**
     * Returns all variables that can be injected into the task.
     *
     * @param bool $onlyPublic
     * @return array
     */
    public function getArguments($onlyPublic = true)
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
