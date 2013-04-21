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
class Task extends Declaration
{
    /**
     * Construct the task with the provided array as task definition
     *
     * @param array $path
     * @param array $node
     */
    public function __construct($path, $node)
    {
        parent::__construct($path);

        if (strpos(end($this->path), '.') !== false) {
            $end = array_pop($this->path);
            $this->path = array_merge($this->path, explode('.', $end));
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
        return join(':', array_slice($this->path, 1));
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


    public function compile(Buffer $buffer)
    {
        parent::compile($buffer);
        if (substr($this->getName(), 0, 1) !== '_') {
            $buffer
                ->writeln('$z->addCommand(')->indent(1)
                ->write('new \Zicht\Tool\Command\TaskCommand(')
                ->asPhp($this->getName())
                ->raw(',')
                ->asPhp($this->getArguments(true))
                ->raw(',')
                ->asPhp($this->getHelp() ? $this->getHelp() : "(no help available for this task)")
                ->raw(')')->eol()
                ->indent(-1)->writeln(');')
            ;
        }
    }


    /**
     * Compile the node
     *
     * @param Buffer $buffer
     * @return void
     */
    public function compileBody(Buffer $buffer)
    {
        $taskName = Util::toPhp($this->path);

        foreach ($this->taskDef['set'] as $node) {
            $node->compile($buffer);
        }
        $buffer->writeln(sprintf('$z->notify(%s, "start");', $taskName));
        $buffer->writeln('try {')->indent(1);
        $hasUnless = false;
        foreach (array('pre', 'do', 'post') as $scope) {
            if ($scope === 'do') {
                if (!empty($this->taskDef['unless'])) {
                    $buffer->write('if (!$z->resolve(array(\'force\')) && (');
                    $this->taskDef['unless']->compile($buffer);
                    $buffer->raw(')) {')->eol()->indent(1);

                    $echoStr = sprintf('echo "%s skipped"', join('.', $this->path));
                    $buffer->writeln(sprintf('$z->cmd(%s);', Util::toPhp($echoStr)));
                    $buffer->indent(-1)->writeln('} else {')->indent(1);
                    $hasUnless = true;
                }
                if (!empty($this->taskDef['assert'])) {
                    $buffer->write('if (!(');
                    $this->taskDef['assert']->compile($buffer);
                    $buffer->raw(')) {')->eol()->indent(1);
                    $buffer->writeln('throw new \RuntimeException("Assertion failed");');
                    $buffer->indent(-1)->writeln('}');
                }
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
        $buffer->indent(-1)->writeln('} catch (\Exception $e) {')->indent(1);
        $buffer->writeln(sprintf('throw new \RuntimeException("While executing task %s", 0, $e);', $taskName));
        $buffer->indent(-1)->writeln('}');
        $buffer->writeln(sprintf('$z->notify(%s, "end");', $taskName));
        $buffer->writeln('return $ret;');
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
