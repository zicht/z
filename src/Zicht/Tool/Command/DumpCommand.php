<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */
namespace Zicht\Tool\Command;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

class DumpCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('z:dump')
            ->setHelp('Dumps the container as PHP')
        ;
    }

    public function run(InputInterface $input, OutputInterface $output)
    {
        $output->writeln($this->container['__definition']);
        if ($output->getVerbosity() > 1) {
            $output->writeln(\Symfony\Component\Yaml\Yaml::dump($this->container['__config']));
        }
    }
}