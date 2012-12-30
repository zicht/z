<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Command;
use \Symfony\Component\Console\Command\Command;

use \Symfony\Component\Console\Input\InputOption;
use \Zicht\Tool\Task\TaskInterface;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to execute a specific task
 */
class TaskCommand extends BaseCommand
{
    /**
     * Executes the specified task
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return mixed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('explain')) {
//            $sh = $input->getOption('sh');
            $this->container['executor'] = $this->container->protect(
                function($exec) use($output/*, $sh*/) {
//                    if ($sh) {
                        $exec = ' ( ' . rtrim($exec, "\n") . ' ); ';
//                    }
                    $output->writeln($exec);
                }
            );
        }

        foreach ($this->getDefinition()->getArguments() as $arg) {
            if ($arg->getName() === 'command') {
                continue;
            }
            $this->container[$arg->getName()]= $input->getArgument($arg->getName());
        }

        return $this->container['tasks.' . $this->getName()];
    }
}