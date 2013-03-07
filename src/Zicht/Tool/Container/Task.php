<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Container;

use \Zicht\Tool\Script\Buffer;
use \Zicht\Tool\Script\Node\Node;
use \Zicht\Tool\Util;

/**
 * Compilable node that represents an executable task
 */
class Task implements Node
{
    /**
     * Construct the task with the provided array as task definition
     *
     * @param array $path
     * @param array $node
     */
    public function __construct($path, $node)
    {
        $this->name = $path;
        if (strpos(end($this->name), '.') !== false) {
            $end = array_pop($this->name);
            $this->name = array_merge($this->name, explode('.', $end));
        }
        $this->taskDef = $node;
    }


    /**
     * Returns the task name.
     *
     * @return string
     */
    public function getName()
    {
        return join(':', array_slice($this->name, 1));
    }


    /**
     * Returns the task help
     *
     * @return mixed
     */
    public function getHelp()
    {
        return $this->taskDef['help'];
    }


    /**
     * Compile the node
     *
     * @param Buffer $buffer
     * @return void
     */
    public function compile(Buffer $buffer)
    {
        $taskName = Util::toPhp($this->name);

        $buffer
            ->writeln('$z->decl(')->indent(1)->writeln($taskName . ',')
            ->writeln('function($z) {')->indent(1);
        ;
        foreach ($this->taskDef['set'] as $node) {
            $node->compile($buffer);
        }
        $buffer->writeln(sprintf('$z->notify(%s, "start");', $taskName));

        $hasUnless = false;
        foreach (array('pre', 'do', 'post') as $scope) {
            if ($scope === 'do' && !empty($this->taskDef['unless'])) {
                $buffer->write('if (!$z->resolve(array(\'force\')) && ($_unless = (');
                $this->taskDef['unless']->compile($buffer);
                $buffer->raw('))) {')->eol()->indent(1);

                $echoStr = sprintf('echo "%s skipped"', join('.', $this->name));
                $buffer->writeln(sprintf('$z->cmd(%s);', Util::toPhp($echoStr)));
                $buffer->indent(-1)->writeln('} else {')->indent(1);
                $hasUnless = true;
            }
            foreach ($this->taskDef[$scope] as $cmd) {
                $cmd->compile($buffer);
            }
            if ($hasUnless && $scope == 'post') {
                $buffer->indent(-1)->writeln('}');
            }
        }
        if (!empty($this->taskDef['yield'])) {
            $buffer->writeln('$ret = ');
            $this->taskDef['yield']->compile($buffer);
            $buffer->write(';');
        } else {
            $buffer->writeln('$ret = null;');
        }
        $buffer->writeln(sprintf('$z->notify(%s, "end");', $taskName));
        $buffer->writeln('return $ret;');
        $buffer->indent(-1)->writeln('}')->indent(-1);
        $buffer->writeln(');');
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
                if ($expr->conditional) {
                    // if the part after the question mark is empty, the variable is assumed to be required
                    // for execution of the task
                    $ret[$name] = ($expr->nodes[0] === null);
                }
            }
        }
        return $ret;
    }
}
