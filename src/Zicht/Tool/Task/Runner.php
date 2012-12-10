<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Tool\Task;

class Runner
{
    function __construct(Builder $builder, Context $context) {
        $this->builder = $builder;
        $this->context = $context;
    }


    function run($tasks)
    {
        $exec = new TaskList($this->builder);

        foreach ($tasks as $task) {
            $exec->addTask($task);
        }

        foreach ($exec as $task) {
            echo "Executing task {$task->getName()}\n";
            $task->setExecutionContext($this->context);
            $task->execute();
        }
    }
}