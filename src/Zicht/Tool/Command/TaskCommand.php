<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Command;

use \Symfony\Component\Console\Command\Command;
use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output;
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
    public function __construct($name, $arguments, $flags, $help)
    {
        parent::__construct($name);

        $this->flags = $flags;

        foreach ($arguments as $name => $required) {
            $this->addArgument(
                $name,
                $required
                    ? InputArgument::REQUIRED
                    : InputArgument::OPTIONAL
            );
        }
        foreach ($flags as $name => $value) {
            $this
                ->addOption('no-' . $name, '', InputOption::VALUE_NONE, 'Toggle ' . $name . ' flag off')
                ->addOption('with-' . $name, '', InputOption::VALUE_NONE, 'Toggle ' . $name . ' flag on')
            ;
        }
        $this->setHelp($help ? $help : '(no help available for this task)');
        $this->setDescription(preg_replace('/^([^\n]*).*/s', '$1', $help));
    }


    /**
     * @{inheritDoc}
     */
    public function getSynopsis()
    {
        $ret = parent::getSynopsis();

        foreach ($this->flags as $name => $value) {
            $i = 0;
            $ret = preg_replace_callback(
                '/\[--(no|with)-' . $name . '\]/',
                function() use(&$i, $name) {
                    if ($i ++ == 0) {
                        return '[--[no|with]-' . $name .']';
                    }
                    return '';
                },
                $ret
            );
        }

        return $ret;
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
            ->addOption('plugin', '', InputOption::VALUE_REQUIRED, 'Load additional plugins on-the-fly')
        ;
    }


    /**
     * Executes the specified task
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return mixed
     */
    protected function execute(InputInterface $input, Output\OutputInterface $output)
    {
        foreach ($this->getDefinition()->getArguments() as $arg) {
            if ($arg->getName() === 'command') {
                continue;
            }
            if ($input->getArgument($arg->getName())) {
                $this->container->set(explode('.', $arg->getName()), $input->getArgument($arg->getName()));
            }
        }
        foreach ($this->flags as $name => $value) {
            $this->container->set(explode('.', $name), $value);
            if ($input->getOption('no-' . $name) && $input->getOption('with-' . $name)) {
                throw new \InvalidArgumentException("Cannot pass both --no-{$name} and --with-{$name} options, they are mutually exclusive");
            }
            if ($input->getOption('no-' . $name)) {
                $this->container->set(explode('.', $name), false);
            } elseif($input->getOption('with-' . $name)) {
                $this->container->set(explode('.', $name), true);
            }
        }

        $this->preflightCheck($output);

        return $this->container->resolve(array_merge(array('tasks'), explode(':', $this->getName())));
    }


    protected function preflightCheck($output)
    {
        try {
            $dry = clone $this->container;
            $dry->set('explain', true);
            $dry->output = new Output\NullOutput();
            $dry->resolve(array_merge(array('tasks'), explode(':', $this->getName())));
        } catch (\Exception $e) {
            $output->writeln("<fg=red>Error: </fg=red> preflight check failed with exception <comment>\"{$e->getMessage()}\"</comment>\n");
            throw $e;
        }
    }
}