<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Task;

use \IteratorAggregate;

class TaskList implements \IteratorAggregate
{
    protected $tasks = array();

    function __construct(Builder $builder)
    {
        $this->builder = $builder;
    }


    function addTask($name)
    {
        if (is_string($name)) {
            $task = $this->builder->build($name);
        } else {
            $task = $name;
        }
        $this->tasks[]= $task;
        foreach ($task->getDepends() as $name) {
            if (!array_key_exists($name, $this->tasks)) {
                $this->addTask($name);
            }
        }
    }


    function getIterator() {
        $this->sort();
        return new \ArrayIterator($this->tasks);
    }


    function sort() {
        $originalSort = array_map(function($t) { return $t->getName(); }, $this->tasks);

        usort($this->tasks, function(TaskInterface $taskA, TaskInterface $taskB) use($originalSort) {
            if (in_array($taskA->getName(), $taskB->getDepends())) {
                return -1;
            } elseif (in_array($taskB->getName(), $taskA->getDepends())) {
                return 1;
            }

            return array_search($taskA->getName(), $originalSort)
                > array_search($taskB->getName(), $originalSort)
                ? 1
                : -1
            ;
        });

        $wouldHaveExecuted = array();
        foreach ($this->tasks as $task) {
            foreach ($task->getDepends() as $name) {
                if (!in_array($name, $wouldHaveExecuted)) {
                    throw new DependencyResolutionError("Task $name could not be resolved. Circular dependency?");
                }
            }
            $wouldHaveExecuted[]= $task->getName();
        }
    }
}
