<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Tool\Command;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

class ExplainCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('z:explain')
            ->setHelp('Explains the commands that are executed')
            ->addArgument('task', \Symfony\Component\Console\Input\InputArgument::REQUIRED, 'The task name to explain')
            ->addOption('sh', '', \Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Make the output sh ready, i.e. execute each line in a subshell.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sh = $input->getOption('sh');
        $this->container['executor'] = $this->container->protect(function($exec) use($output, $sh) {
            if ($sh) {
                $exec = ' ( ' . rtrim($exec, "\n") . ' ); ';
            }
            $output->writeln($exec);
        });

        return $this->container['tasks.' . $input->getArgument('task')];
    }
}