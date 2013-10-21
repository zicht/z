<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Tool\Command;

use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\Console\Command\Command;
use \Symfony\Component\Yaml\Yaml;
use \Zicht\Tool\Script\Buffer;

/**
 * Dumps the container
 */
class DumpCommand extends BaseCommand
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
            ->setName('z:dump')
            ->setHelp('Dumps container values')
            ->setDescription('Dumps container values')
        ;
    }


    /**
     * Executes the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(Yaml::dump($this->container->getValues(), 5, 4));
    }
}