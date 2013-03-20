<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Container;

use Zicht\Tool\Script\Compiler as ScriptCompiler;
use Zicht\Tool\Script\Buffer;

/**
 * Compilable node that represents an executable task
 */
class Task
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


    function getHelp()
    {
        return $this->taskDef['help'];
    }


    /**
     * Compile the node
     *
     * @param Flattener $compiler
     * @param int $indent
     * @return string
     */
    public function compile(Buffer $buffer)
    {
        $scriptcompiler = new ScriptCompiler(new \Zicht\Tool\Script\Parser());
        $exprcompiler  = new ScriptCompiler(new \Zicht\Tool\Script\Parser\Expression(), new \Zicht\Tool\Script\Tokenizer\Expression());

        $taskName = var_export($this->name, true);

        $buffer
            ->indent(1)->writeln('function($z) {')
            ->writeln(sprintf('$z->notify(%s, "start");', $taskName))
        ;

        foreach ($this->taskDef['set'] as $name => $value) {
            if ($value && preg_match('/^\?\s*(.*)/', trim($value), $m)) {
                $m[1] = trim($m[1]);

                $buffer->indent(1)->writeln(sprintf('if (!$z->has(%s)) {', var_export($name, true)));
                if (!$m[1]) {
                    $buffer->writeln(sprintf(
                        'throw new \RuntimeException(\'required variable %s is not defined\');',
                        $name
                    ));
                } else {
                    $buffer->writeln(
                        sprintf(
                            '$z->set(%s, %s);',
                            var_export($name, true),
                            $exprcompiler->compile($m[1])
                        )
                    );
                }
                $buffer->indent(-1)->writeln('}');
            } else {
                $buffer->writeln(
                    sprintf(
                        '$z->set(%s, %s);',
                        var_export($name, true),
                        $exprcompiler->compile($value)
                    )
                );
            }
        }

        $hasUnless = false;
        foreach (array('pre', 'do', 'post') as $scope) {
            if ($scope === 'do' && !empty($this->taskDef['unless'])) {
                $unlessExpr = $exprcompiler->compile($this->taskDef['unless']);
                $buffer
                    ->write('if (!$z->resolve(\'force\') && ($_unless = (')
                    ->write($unlessExpr)
                    ->writeln('))) {')
                ;

                $buffer->writeln('$z->cmd(sprintf(' . var_export(
                    sprintf(
                        'echo "%s skipped, because %s" evaluates to \'%%s\'',
                        $this->name,
                        $this->taskDef['unless']
                    ),
                    true
                ) . ', var_export($_unless, true)));');

                $buffer->writeln('} else {');
                $hasUnless = true;
            }
            foreach ($this->taskDef[$scope] as $cmd) {
                $buffer->writeln($scriptcompiler->compile($cmd));
            }
            if ($hasUnless && $scope == 'post') {
                $buffer->writeln('}');
            }
        }
        if (!empty($this->taskDef['yield'])) {
            $buffer->writeln('$ret = ' . $exprcompiler->compile($this->taskDef['yield']) . ';');
        } else {
            $buffer->writeln('$ret = null;');
        }
        $buffer->writeln(sprintf('$z->notify(%s, "end");', $taskName));
        $buffer->writeln('return $ret;');
        $buffer->writeln('}')->indent(-1);
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
