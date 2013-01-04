<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Command;

use \Symfony\Component\DependencyInjection\ContainerInterface;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\Yaml\Yaml;

/**
 * Command to initialize a z file
 */
class EvalCommand extends BaseCommand
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
            ->setName('z:eval')
            ->addArgument('expression', InputArgument::REQUIRED, 'Expression to evaluate')
            ->setHelp('Evaluate an expression in the Z context')
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
        $output->writeln($this->container->evaluate($input->getArgument('expression')));
    }
}