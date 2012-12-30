<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Tool\Command;

use \Symfony\Component\DependencyInjection\ContainerInterface;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\Console\Command\Command;

/**
 * Command to explain a task
 */
class ExplainCommand extends BaseCommand
{
    /**
     * Configures the command
     *
     * @return void
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('z:explain')
            ->setHelp('Explains the commands that are executed')
            ->addArgument('task', InputArgument::REQUIRED, 'The task name to explain')
            ->addOption(
                'sh',
                '',
                InputOption::VALUE_NONE,
                'Make the output sh ready, i.e. execute each line in a subshell.'
            )
        ;
    }


    /**
     * Executes the command
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sh = $input->getOption('sh');
        $this->container['executor'] = $this->container->protect(
            function($exec) use($output, $sh) {
                if ($sh) {
                    $exec = ' ( ' . rtrim($exec, "\n") . ' ); ';
                }
                $output->writeln($exec);
            }
        );

        return $this->container['tasks.' . $input->getArgument('task')];
    }
}