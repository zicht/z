<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Command;
use \Symfony\Component\Console\Command\Command;

use Symfony\Component\Console\Input\InputArgument;
use Zicht\Tool\Task\TaskInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TaskCommand extends BaseCommand
{
    protected $task;

    function __construct(TaskInterface $task, $container) {
        $this->task = $task;
        parent::__construct($container);
    }


    function configure()
    {
        $this
            ->setName(str_replace('.', ':', $this->task->getName()))
            ->addArgument('environment', InputArgument::OPTIONAL, 'The environment to connect to')
        ;
    }



    protected function execute(InputInterface $input, OutputInterface $output) {
        if ($env = $input->getArgument('environment')) {
            $this->container->get('task_context')->setEnvironment($env);
        }
        $this->container->get('task_runner')->run(array($this->task));
    }
}