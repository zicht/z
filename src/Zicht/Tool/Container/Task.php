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
     * @param string $name
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
        $exprcompiler  = new ScriptCompiler(new \Zicht\Tool\Script\Parser\Expression(), new \Zicht\Tool\Script\Tokenizer\Expression());

        $eol = function($inc = 0) use(&$indent) {
            if ($inc == 1) {
                $indent ++;
            }
            $ret = PHP_EOL . str_repeat('    ', $indent);
            if ($inc == -1) {
                $indent --;
            }
            return $ret;
        };

        $ret = '$' . $compiler->getContainerName() . '->share(function($z) {' . $eol();

        $taskName = var_export($this->name, true);
        $ret .= sprintf('$z->notify(%s, "start");', $taskName) . $eol();

        foreach ($this->taskDef['set'] as $name => $value) {
            if ($value && preg_match('/^\?\s*(.*)/', trim($value), $m)) {
                $ret .= 'if (!isset($z[' . var_export($name, true) . '])) {' . $eol(1);
                if (!$m[1]) {
                    $ret .= sprintf(
                        'throw new \RuntimeException(\'required variable %s is not defined\');',
                        $name,
                        true
                    ) . $eol(-1);
                } else {
                    $ret .= sprintf('$z[%s] = %s;',
                        var_export($name, true),
                        $exprcompiler->compile($m[1])
                    ) . $eol(-1);
                }
                $ret .= '}' . $eol();
            } else {
                $ret .= sprintf(
                    '$z[%s] = %s;',
                    var_export($name, true),
                    $exprcompiler->compile($value)
                ) . $eol();
            }
        }

        $hasUnless = false;
        foreach (array('pre', 'do', 'post') as $scope) {
            $ret .= sprintf('$z->notify(%s, %s);', $taskName, var_export('before_' . $scope, true)) . $eol();
            if ($scope === 'do' && !empty($this->taskDef['unless'])) {
                $ret .= 'if (!$z[\'force\'] && (' . $exprcompiler->compile($this->taskDef['unless']) . ')) {' . $eol(1);
                $ret .= '$z->output->writeln("<comment>" . ' . var_export($this->taskDef['unless'], true ) . ' . "</comment>, skipped ' . $this->name . '");' . $eol(-1);
                $ret .= '} else {' . $eol(1);
                $hasUnless = true;
            }
            foreach ($this->taskDef[$scope] as $cmd) {
                $ret .= $scriptcompiler->compile($cmd) . $eol();
            }
            if ($hasUnless && $scope == 'post') {
                $ret .= '}' . $eol(-1);
            }
            $ret .= sprintf('$z->notify(%s, %s);', $taskName, var_export('after_' . $scope, true)) . $eol();
        }
        if (!empty($this->taskDef['yield'])) {
            $ret .= '$ret = ' . $exprcompiler->compile($this->taskDef['yield']) . ';' . $eol();
        } else {
            $ret .= '$ret = null;' . $eol();
        }
        $ret .= sprintf('$z->notify(%s, "end");', $taskName) . $eol();
        $ret .= 'return $ret;' . $eol(-1);
        $ret .= '})' . $eol();
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
