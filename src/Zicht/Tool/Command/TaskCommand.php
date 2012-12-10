<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Command;
use \Symfony\Component\Console\Command\Command;

use Symfony\Component\Console\Input\InputOption;
use Zicht\Tool\Task\TaskInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TaskCommand extends BaseCommand
{
    protected $taskName;

    function __construct($taskName, $container) {
        $this->taskName = $taskName;
        parent::__construct($container);
    }


    function configure()
    {
        $this
            ->setName(str_replace('.', ':', $this->taskName))
            ->addOption('simulate', 's', InputOption::VALUE_NONE, "Simulate the task")
        ;
    }



    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->container->get('task_runner')->run(array($this->taskName), $input->getOption('simulate'));
    }
}