<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool;

use \Symfony\Component\Console\Application as BaseApplication;
use \Symfony\Component\Yaml\Yaml;
use \Symfony\Component\Config\FileLocator;
use \Symfony\Component\Config\Definition\Processor;
use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Command\Command;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\Console\Output\ConsoleOutput;
use \Symfony\Component\Console\Input\ArgvInput;

use \Zicht\Tool\Command as Cmd;
use \Zicht\Tool\Command\TaskCommand;
use \Zicht\Tool\Container\Configuration;
use \Zicht\Tool\Container\Container;
use \Zicht\Tool\Container\Compiler;
use \Zicht\Tool\Container\Preprocessor;
use \Zicht\Tool\Version;
use \Zicht\Tool\Container\Task;

/**
 * Z CLI Application
 */
class Application extends BaseApplication
{
    protected $config;
    protected $container;
    protected $tasks;

    /**
     * Constructor, initializes the application, container and the commands
     */
    public function __construct()
    {
        parent::__construct('z - The Zicht Tool', Version::VERSION);

        $this->initContainer();

        $this->add(new Cmd\DumpCommand());
        $this->add(new Cmd\InitCommand());
        $this->add(new Cmd\EvalCommand());

        /** @var $task \Zicht\Tool\Container\Task */
        foreach ($this->tasks as $name => $task) {
            // if a tasks is prefixed with an underscore, it is considered an internal task
            if (substr($name, 0, 1) !== '_') {
                $cmd = new TaskCommand(str_replace('.', ':', $name));
                $cmd->setContainer($this->container);
                foreach ($task->getArguments() as $var => $isRequired) {
                    $cmd->addArgument($var, $isRequired ? InputArgument::REQUIRED : InputArgument::OPTIONAL);
                }
                $cmd->addOption('explain', '', InputOption::VALUE_NONE, 'Explains the commands that are executed');
                $cmd->addOption('force', 'f', InputOption::VALUE_NONE, 'Force execution of otherwise skipped tasks');
                $this->add($cmd);
            }
        }
    }


    /**
     * Injects the container into the command if it expects one.
     *
     * @param \Symfony\Component\Console\Command\Command $command
     * @return \Symfony\Component\Console\Command\Command
     */
    public function add(Command $command)
    {
        if ($command instanceof Cmd\BaseCommand) {
            $command->setContainer($this->container);
        }
        return parent::add($command);
    }


    /**
     * Adds the environment to the input definition
     *
     * @return \Symfony\Component\Console\Input\InputDefinition
     */
    protected function getDefaultInputDefinition()
    {
        $ret = parent::getDefaultInputDefinition();
        $ret->addOption(new InputOption('env',      'e', InputOption::VALUE_REQUIRED, 'Environment', null));
        return $ret;
    }


    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        if (null === $input) {
            $input = new ArgvInput();
        }
        if (null === $output) {
            $output = new ConsoleOutput();
        }

        $this->container['console_output']= $output;
        $this->container['console_input']= $input;
        $this->container['console_dialog_helper']= $this->getHelperSet()->get('dialog');

        if (true === $input->hasParameterOption(array('--verbose', '-v'))) {
            $this->container['verbose'] = true;
        }

        return parent::run($input, $output);
    }


    /**
     * Initializes the container.
     *
     * @return void
     *
     * @throws \UnexpectedValueException
     */
    public function initContainer()
    {
        $zFileLocator = new FileLocator(array(getcwd(), getenv('HOME') . '/.config/z/'));
        $pluginLocator = new FileLocator(__DIR__ . '/Resources/plugins');

        $loader = new FileLoader($pluginLocator);

        try {
            $zfiles = $zFileLocator->locate('z.yml', null, false);
        } catch (\InvalidArgumentException $e) {
            $zfiles = array();
        }
        foreach ($zfiles as $file) {
            $loader->load($file);
        }

        $pluginFiles = $loader->getPlugins();
        $plugins = array();
        foreach ($pluginFiles as $name => $file) {
            require_once $file;
            $className = sprintf('Zicht\Tool\Plugin\%s\Plugin', ucfirst($name));
            $class = new \ReflectionClass($className);
            if (!$class->implementsInterface('Zicht\Tool\PluginInterface')) {
                throw new \UnexpectedValueException("The class $className is not a 'Zicht\\Tool\\PluginInterface'");
            }
            $plugins[$name] = $class->newInstance();
        }

        $processor = new Processor();
        $this->config = $processor->processConfiguration(
            new Configuration($plugins),
            $loader->getConfigs()
        );

        $compiler = new Compiler('container');
        $cp = $this->config;
        $this->tasks = array();
        foreach ($cp['tasks'] as $i => $task) {
            $this->tasks[$i] = $cp['tasks.' . $i] = new Task($task, $i);
        }
        $code = $compiler->compile($cp);

        $container = new Container();
        $container->setPlugins($plugins);
        eval($code);
        $this->container = $container;

        $this->container['__definition'] = $code;
        $this->container['__config'] = $this->config;
    }
}