<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Tool\Task;

abstract class Task implements TaskInterface
{
    protected $name;

    /**
     * @var ContextInterface
     */
    protected $context = null;

    function setExecutionContext(ContextInterface $context) {
        $this->context = $context;
    }


    function setName($name) {
        $this->name = $name;
    }


    function getName() {
        return $this->name;
    }

    function getPriority() {
        return 0;
    }

    function simulate() {
        $this->context->writeln("Would execute {$this->getName()}");
    }

    /**
     * @return array
     */
    function getDepends() {
        return array();
    }

    function getTriggers() {
        return array();
    }
}
