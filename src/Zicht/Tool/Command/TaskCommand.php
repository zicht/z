<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Command;

use \Symfony\Component\Console\Command\Command;
use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\Console\Input\InputArgument;

/**
 * Command to execute a specific task
 */
class TaskCommand extends BaseCommand
{
    /**
     * Construct the front end command for the specified task name.
     *
     * @param null|string $name
     * @param array $arguments
     * @param string $help
     */
    public function __construct($name, $arguments, $help)
    {
        parent::__construct($name);

        foreach ($arguments as $name => $required) {
            $this->addArgument(
                $name,
                $required
                    ? InputArgument::REQUIRED
                    : InputArgument::OPTIONAL
            );
        }
        $this->setHelp($help ? $help : '(no help available for this task)');
        $this->setDescription(preg_replace('/^([^\n]*).*/s', '$1', $help));
    }


    /**
     * Adds the default '--explain' and '--force' options
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->addOption('explain', '', InputOption::VALUE_NONE, 'Explains the commands that would be executed.')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force execution of otherwise skipped tasks.')
        ;
    }


    /**
     * Executes the specified task
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return mixed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->getDefinition()->getArguments() as $arg) {
            if ($arg->getName() === 'command') {
                continue;
            }
            if ($input->getArgument($arg->getName())) {
                $this->container->set(explode('.', $arg->getName()), $input->getArgument($arg->getName()));
            }
        }

        return $this->container->resolve(array_merge(array('tasks'), explode(':', $this->getName())));
    }
}