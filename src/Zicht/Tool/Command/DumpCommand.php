<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Tool\Command;

use \Symfony\Component\DependencyInjection\ContainerInterface;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\Console\Command\Command;
use \Symfony\Component\Yaml\Yaml;

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
            ->setHelp('Dumps the container as PHP')
        ;
    }


    /**
     * Executes the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln($this->container['__definition']);
        if ($output->getVerbosity() > 1) {
            $output->writeln(Yaml::dump($this->container['__config']));
        }
    }
}