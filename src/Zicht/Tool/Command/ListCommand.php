<?php

namespace Zicht\Tool\Command;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input;
use Symfony\Component\Console\Command\Command;

class ListCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('z:list')
            ->setAliases(array('list'))
            ->setDescription('Lists available commands')
        ;
    }

    protected function execute(Input\InputInterface $input, OutputInterface $output)
    {
        $descriptor = new Descriptor\TextDescriptor();
        $descriptor->describe($output, $this->getApplication());
    }
}
