<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\DependencyInjection\ContainerInterface;
use \Symfony\Component\Yaml\Yaml;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use \Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use \Symfony\Component\Config\FileLocator;
use \Symfony\Component\Config\Definition\Processor;
use Zicht\Tool\Command\TaskCommand;
use \Symfony\Component\Console\Input\InputArgument;

class Application extends BaseApplication
{
    protected $config;
    protected $container;

    function __construct() {
        parent::__construct('z - The Zicht Tool', \Zicht\Tool\Version::VERSION);

        $this->initContainer();

        foreach ($this->config['tasks'] as $name => $options) {
            $class = $this->container->get('task_resolver')->resolve($name);
            $parameters = call_user_func(array($class, 'uses'));

            $list = new \Zicht\Tool\Task\TaskList($this->container->get('task_builder'), $this->config['tasks']);
            $list->addTask($name);

            $command = new TaskCommand($name, $this->container);
            foreach ($parameters as $name) {
                $required = true;
                foreach ($list as $task) {
                    if (in_array($name, $task->provides())) {
                        $required = false;
                        break;
                    }
                }
                $command->addArgument($name, $required ? InputArgument::REQUIRED : InputArgument::OPTIONAL);
            }
            $this->add($command);
        }
    }


    function initContainer() {
        $locator = new FileLocator(array(__DIR__ . '/Resources/', getcwd()));
        $configs = array();
        foreach ($locator->locate('z.yml', null, false) as $file) {
            $configs[]= Yaml::parse($file);
        }

        $processor = new Processor();
        $this->config = $processor->processConfiguration(new Configuration(), $configs);

        $params = new ParameterBag($this->config);
        $builder = new ContainerBuilder($params);
        $loader = new XmlFileLoader($builder, new FileLocator(__DIR__ . '/Resources/'));
        $loader->load('services.xml');
        $builder->compile();

        $this->container = $builder;
    }
}