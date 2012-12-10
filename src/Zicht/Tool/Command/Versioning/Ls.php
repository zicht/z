<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Command\Versioning;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class Ls extends BaseVersioningCommand
{
    protected function configure() {
        $this
            ->setName('versioning:list')
            ->setDescription("Export the versioning url")
            ->setAliases(array('ls'))
            ->addOption('type', 't', InputArgument::OPTIONAL, "Type (tags or branches)", null)
        ;
    }


    protected function execute(InputInterface $input, OutputInterface $output) {
        $output->writeln("Available versions: ");
        foreach ($this->getVersioning()->listVersions($input->getOption('type')) as $version) {
            if ($output->getVerbosity() > 1) {
                $output->writeln(sprintf(" - %s <info>%s</info> (@%s)", $version->getType(), $version->getName(), $version->getPeg()));
            } else {
                $output->writeln(sprintf(" - %s <info>%s</info>", $version->getType(), $version->getName(), $version->getPeg()));
            }
        }
    }
}