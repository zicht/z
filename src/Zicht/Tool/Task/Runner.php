<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Tool\Task;

class Runner
{
    function __construct(Builder $builder, Context $context, $options) {
        $this->builder = $builder;
        $this->context = $context;
        $this->taskOptions = $options;
    }


    function run($tasks, $simulate = false)
    {
        $exec = new TaskList($this->builder, $this->taskOptions);

        foreach ($tasks as $task) {
            $exec->addTask($task);
        }

        foreach ($exec as $task) {
            $task->setExecutionContext($this->context);
            if ($simulate) {
                $this->context->writeln("Simulating task {$task->getName()}");
                $task->simulate();
            } else {
                $this->context->writeln("Executing task {$task->getName()}");
                $task->execute();
            }
        }
    }
}