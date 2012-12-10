<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Tool\Task;

class TaskResolutionError extends \UnexpectedValueException
{
    public function __construct($name, $classNames) {
        $tried = join(", ", $classNames);
        parent::__construct(sprintf("Task not found: {$name} ({$tried})"));
    }
}