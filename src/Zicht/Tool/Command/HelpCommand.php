<?php

namespace Zicht\Tool\Command;

use Symfony\Component\Console\Helper\DescriptorHelper;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input;
use Symfony\Component\Console\Command\Command;

class HelpCommand extends Command
{
    private $command;

    protected function configure()
    {
        $this->ignoreValidationErrors();

        $this
            ->setName('z:help')
            ->setDescription('Shows help')
            ->setDefinition(
                array(
                    new Input\InputArgument('command_name', Input\InputArgument::OPTIONAL, 'The command name', 'z:help'),
                )
            )
        ;
    }


    /**
     * Sets the command
     *
     * @param Command $command The command to set
     */
    public function setCommand(Command $command)
    {
        $this->command = $command;
    }


    protected function execute(Input\InputInterface $input, OutputInterface $output)
    {
        if (null === $this->command) {
            $this->command = $this->getApplication()->find($input->getArgument('command_name'));
        }

        $helper = new DescriptorHelper();
        $helper->describe($output, $this->command, array(
            'format' => 'txt',
            'raw_text' => false,
        ));
    }
}
