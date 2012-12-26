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
    protected function execute(InputInterface $input, OutputInterface $output) {
        return $this->container['tasks.' . $this->getName()];
    }
}