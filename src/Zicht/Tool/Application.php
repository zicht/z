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

use \Zicht\Tool\Command as Cmd;
use \Zicht\Tool\Command\TaskCommand;
use \Zicht\Tool\Container\Configuration;
use \Zicht\Tool\Container\Container;
use \Zicht\Tool\Container\Compiler;
use \Zicht\Tool\Container\Preprocessor;
use \Zicht\Tool\Version;

/**
 * Z CLI Application
 */
class Application extends BaseApplication {
    protected $config;
    protected $container;

    /**
     * Constructor, initializes the application, container and the commands
     */
    public function __construct()
    {
        parent::__construct('z - The Zicht Tool', Version::VERSION);

        $this->initContainer();

        $this->add(new Cmd\DumpCommand());
        $this->add(new Cmd\ExplainCommand());
        $this->add(new Cmd\InitCommand());

        /** @var $task \Zicht\Tool\Container\Task */
        foreach ($this->config['tasks'] as $name => $task) {
            if (substr($name, 0, 1) !== '_') {
                $cmd = new TaskCommand($name);
                $cmd->setContainer($this->container);
                foreach ($task->getVariables() as $var => $isRequired) {
                    $cmd->addArgument($var, $isRequired ? InputArgument::REQUIRED : InputArgument::OPTIONAL);
                }
                $cmd->addOption('explain', '', InputOption::VALUE_NONE, 'Explains the commands that are executed');
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
        $ret->addOption(new InputOption('env', 'e', InputOption::VALUE_REQUIRED, 'Environment', null));
        return $ret;
    }


    /**
     * Initializes the container.
     *
     * @return void
     */
    public function initContainer()
    {
        $locator = new FileLocator(array(__DIR__ . '/Resources/', getcwd()));
        $configs = array();
        foreach ($locator->locate('z.yml', null, false) as $file) {
            $configs[]= Yaml::parse($file);
        }

        $processor = new Processor();
        $preprocessor = new Preprocessor();
        $this->config = $preprocessor->preprocess($processor->processConfiguration(new Configuration(), $configs));

        $compiler = new Compiler('container');
        $code = $compiler->compile($this->config);

        $container = new Container();
        eval($code);
        $this->container = $container;

        $this->container['__definition'] = $code;
        $this->container['__config'] = $this->config;
    }
}