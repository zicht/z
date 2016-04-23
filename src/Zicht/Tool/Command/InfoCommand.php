<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Tool\Command;

use Symfony\Component\Console\Input;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Command to show some info about the Z container
 */
class InfoCommand extends BaseCommand
{
    /**
     * @{inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('z:info')
            ->setDescription("Prints useful info about the current Z environment")
        ;
    }

    /**
     * @{inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sourceFiles = $this->getContainer()->get(array('z', 'sources'));
        $output->writeln('Z executable used: ' . ZBIN);
        if (count($sourceFiles)) {
            $output->writeln('Loaded source files');
            foreach ($sourceFiles as $file) {
                $output->writeln(" - " . $file);
            }
        } else {
            $output->writeln('No source files loaded');
        }
        $output->writeln('Compiled file: ' . $this->getContainer()->get(array('z', 'cache_file')));
    }
}
