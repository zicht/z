<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Tool\Command;

use \Symfony\Component\Console\Input;
use \Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Yaml\Yaml;

/**
 * Command to show some info about the Z container
 */
class InfoCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('z:info')
            ->setDescription("Prints useful info about the current Z environment")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    }
}