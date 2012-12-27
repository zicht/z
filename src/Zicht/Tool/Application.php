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
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\Console\Command\Command;

use \Zicht\Tool\Command\TaskCommand;
use \Zicht\Tool\Container\Configuration;
use \Zicht\Tool\Container\Container;
use \Zicht\Tool\Container\Compiler;
use \Zicht\Tool\Container\Preprocessor;

class Application extends BaseApplication
{
    protected $config;
    protected $container;

    function __construct() {
        parent::__construct('z - The Zicht Tool', \Zicht\Tool\Version::VERSION);

        $this->initContainer();

        $this->add(new \Zicht\Tool\Command\DumpCommand());
        $this->add(new \Zicht\Tool\Command\ExplainCommand());
        $this->add(new \Zicht\Tool\Command\InitCommand());

        foreach ($this->config['tasks'] as $name => $options) {
            if (substr($name, 0, 1) !== '_') {
                $cmd = new TaskCommand($name);
                $cmd->setContainer($this->container);
                $this->add($cmd);
            }
        }
    }

    public function add(Command $command)
    {
        if ($command instanceof \Zicht\Tool\Command\BaseCommand) {
            $command->setContainer($this->container);
        }
        return parent::add($command);
    }


    protected function getDefaultInputDefinition()
    {
        $ret = parent::getDefaultInputDefinition();
        $ret->addOption(new InputOption('env', 'e', InputOption::VALUE_REQUIRED, 'Environment', null));
        return $ret;
    }



    function initContainer() {
        $locator = new FileLocator(array(__DIR__ . '/Resources/', getcwd()));
        $configs = array();
        foreach ($locator->locate('z.yml', null, false) as $file) {
            $configs[]= Yaml::parse($file);
        }

        $processor = new Processor();
        $this->config = $processor->processConfiguration(new Configuration(), $configs);

        $compiler = new Compiler('container');
        $preprocessor = new Preprocessor();
        $container = new Container();
        $code = $compiler->compile($preprocessor->preprocess($this->config));
        eval($code);
        $this->container = $container;
        $this->container['__definition'] = $code;
        $this->container['__config'] = $this->config;
        return $code;
    }
}