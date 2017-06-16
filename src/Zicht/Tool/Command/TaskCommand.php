<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Application;
use Zicht\Tool\PluginTaskListenerInterface;

/**
 * Command to execute a specific task
 */
class TaskCommand extends BaseCommand
{
    protected $taskName;
    protected $flags;
    protected $opts;
    protected $taskReference;

    /**
     * Construct the front end command for the specified task name.
     *
     * @param null|string $name
     * @param array $arguments
     * @param array $options
     * @param array $flags
     * @param string $help
     */
    public function __construct($name, $arguments, $options, $flags, $help)
    {
        $this->taskReference = array_merge(array('tasks'), explode('.', $name));
        $this->taskName = str_replace(array('.', '_'), array(':', '-'), $name);

        parent::__construct($this->taskName);

        $this->flags = $flags;
        $this->opts = $options;


        // first line of help is the description
        $this->setDescription(preg_replace('/^([^\n]*).*/s', '$1', $help));
        $this->setHelp(trim($help));

        foreach ($arguments as $name => $required) {
            $mode = $required ? InputArgument::REQUIRED : InputArgument::OPTIONAL;

            if ($multiple = ('[]' === substr($name, -2))) {
                $name = substr($name, 0, -2);
                $mode |= InputArgument::IS_ARRAY;
            }

            $this->addArgument($name, $mode);
        }
        foreach ($options as $name) {
            $this
                ->addOption($this->varToName($name), '', InputOption::VALUE_REQUIRED)
            ;
        }
        foreach ($flags as $name => $value) {
            $name = $this->varToName($name);
            $this
                ->addOption($name, '', InputOption::VALUE_NONE)
                ->addOption('no-' . $name, '', InputOption::VALUE_NONE)
            ;
        }
    }

    /**
     * Sets the application instance for this command.
     *
     * @param Application $application An Application instance
     */
    public function setApplication(Application $application = null)
    {
        parent::setApplication($application);

        if (!is_null($application)) {
            try {
                foreach($this->getContainer()->plugins as $plugin) {
                    if ($plugin instanceof PluginTaskListenerInterface) {
                        $name = implode('.', array_slice($this->getTaskReference(), 1));
                        $listeners = $plugin->getTaskListeners();
                        if (in_array($name, array_keys($listeners))) {
                            call_user_func_array(array($plugin, $listeners[$name]), array($this));
                        }
                    }
                }
            } catch (\UnexpectedValueException $e) {}
        }

    }

    /**
     * @{@inheritDoc}
     */
    public function addOption($name, $shortcut = null, $mode = null, $help = null, $default = null)
    {
        $helpTag = ($mode === InputOption::VALUE_NONE) ? "--$name" : "--$name=" . strtoupper($name);
        return parent::addOption($name, $shortcut, $mode, $help ?: $this->parseHelp($helpTag), $default);
    }

    /**
     * @{@inheritDoc}
     */
    public function addArgument($name, $mode = null, $help = null, $default = null)
    {
        return parent::addArgument($name, $mode, $help ?: $this->parseHelp($name), $default);
    }

    /**
     * will add the given option the opt stack so it will resolved for the z plugin.
     *
     * @param string $name
     * @param null $shortcut
     * @param null $mode
     * @param null $help
     * @param null $default
     * @return $this
     */
    public function addResolvableOption($name, $shortcut = null, $mode = null, $help = null, $default = null)
    {
        $this->opts[] = $name;
        return parent::addOption($name, $shortcut, $mode, $help, $default);
    }

    private function parseHelp($name)
    {
        $ret = '';
        $this->setHelp(
            preg_replace_callback(
                sprintf('/\s*%s: (.*)(\n|$)/', $name),
                function ($m) use (&$ret) {
                    $ret = trim($m[1]);
                    return '';
                },
                $this->getHelp()
            )
        );
        return $ret;
    }


    protected function varToName($name)
    {
        return str_replace('_', '-', $name);
    }


    /**
     * @param string $name
     *
     * @return string
     */
    protected function nameToVar($name)
    {
        return str_replace('-', '_', $name);
    }


    /**
     * @{inheritDoc}
     */
    public function getSynopsis($short = false)
    {
        $ret = parent::getSynopsis($short);

        foreach ($this->flags as $name => $value) {
            $i = 0;
            $ret = preg_replace_callback(
                '/\[--(no-)?' . $name . '\]/',
                function () use(&$i, $name) {
                    if ($i++ == 0) {
                        return '[--[no|]-' . $name . ']';
                    }
                    return '';
                },
                $ret
            );
        }

        return $ret;
    }


    /**
     * Adds the default '--explain', '--force', '--plugin' and '--debug' options
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->addOption('explain', '', InputOption::VALUE_NONE, 'Explains the commands that would be executed.')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force execution of otherwise skipped tasks.')
            ->addOption('plugin', '', InputOption::VALUE_REQUIRED, 'Load additional plugins on-the-fly')
            ->addOption('debug', '', InputOption::VALUE_NONE, "Set the debug flag")
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
                $this->getContainer()->set(explode('.', $this->nameToVar($arg->getName())), $input->getArgument($arg->getName()));
            }
        }
        foreach ($this->opts as $opt) {
            $optName = $this->varToName($opt);
            if ($input->getOption($optName)) {
                $this->getContainer()->set(explode('.', $optName), $input->getOption($optName));
            }
        }
        foreach ($this->flags as $name => $value) {
            $varName = explode('.', $name);
            $optName = $this->varToName($name);
            $this->getContainer()->set($varName, $value);

            if ($input->getOption('no-' . $optName) && $input->getOption($optName)) {
                throw new \InvalidArgumentException("Conflicting options --no-{$optName} and --{$optName} supplied. That confuses me.");
            }
            if ($input->getOption('no-' . $optName)) {
                $this->getContainer()->set($varName, false);
            } elseif ($input->getOption($optName)) {
                $this->getContainer()->set($varName, true);
            }
        }

        $callable = $this->getContainer()->get($this->getTaskReference());
        call_user_func($callable, $this->getContainer());
    }

    /**
     * Returns the reference path that points to the task declaration in the container.
     *
     * @return string
     */
    protected function getTaskReference()
    {
        return $this->taskReference;
    }
}
