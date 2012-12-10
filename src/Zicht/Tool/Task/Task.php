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

    function __construct($name, $options) {
        $this->name = $name;
        $this->options = $options;
    }


    function setExecutionContext(ContextInterface $context) {
        $this->context = $context;
    }


    function setName($name) {
        $this->name = $name;
    }


    function getName() {
        return $this->name;
    }


    function simulate() {
        $this->context->writeln("Would execute {$this->getName()}");
    }

    /**
     * @return array
     */
    final function getDepends() {
        if (!isset($this->options['depends'])) {
            return array();
        }
        return $this->options['depends'];
    }

    static function uses()
    {
        return array();
    }

    static function provides()
    {
        return array();
    }
}
