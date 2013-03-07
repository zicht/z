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
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

use \Zicht\Tool\Command as Cmd;
use \Zicht\Tool\Container\Configuration;
use \Zicht\Tool\Container\Container;
use \Zicht\Tool\Container\ContainerBuilder;
use \Zicht\Tool\Version;
use \Zicht\Tool\Script\Buffer;

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
    }


    /**
     * Replaces the default Output class with one specifically for this application
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        return parent::run($input, null !== $output ? $output : new Output\ConsoleOutput());
    }


    /**
     * @{inheritDoc}
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        if (true === $input->hasParameterOption(array('--quiet', '-q'))) {
            $output->setVerbosity(OutputInterface::VERBOSITY_QUIET);
        } elseif (true === $input->hasParameterOption(array('--verbose', '-v'))) {
            $output->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
        }

        list($plugins, $configTree) = $this->getConfig(getenv('ZFILE'), getenv('ZPLUGINDIR'));

        $builder = new ContainerBuilder($configTree);

        $containerNode = $builder->build();
        $container = $this->initContainer($plugins, $containerNode);
        $container->output = $output;

        $container->set('verbose',  $input->hasParameterOption(array('--verbose', '-v')));
        $container->set('force',    $input->hasParameterOption(array('--force', '-f')));
        $container->set('explain',  $input->hasParameterOption(array('--explain')));

        $this->add(new Cmd\DumpCommand($containerNode, $configTree));

        foreach ($containerNode->getTasks() as $task) {
            $name = $task->getName();
            if (substr($name, 0, 1) !== '_') {
                $this->addTaskCommand($container, $task);
            }
        }
        $container->console_dialog_helper = $this->getHelperSet()->get('dialog');

        return parent::doRun($input, $output);
    }


    /**
     * Adds a command for the specified task.
     *
     * @param Container $container
     * @param \Zicht\Tool\Container\Task $task
     * @return void
     */
    public function addTaskCommand($container, $task)
    {
        $cmd = new Cmd\TaskCommand($container, $task->getName());
        foreach ($task->getArguments() as $var => $isRequired) {
            $cmd->addArgument($var, $isRequired ? InputArgument::REQUIRED : InputArgument::OPTIONAL);
        }
        $cmd->addOption('explain', '', InputOption::VALUE_NONE, 'Explains the commands that would be executed.');
        $cmd->addOption('force', 'f', InputOption::VALUE_NONE, 'Force execution of otherwise skipped tasks.');
        $cmd->setHelp($task->getHelp());
        $cmd->setDescription(preg_replace('/^([^\n]*).*/s', '$1', $task->getHelp()));
        $this->add($cmd);
    }


    /**
     * Initializes the container.
     *
     * @param array $plugins
     * @param \Zicht\Tool\Container\ContainerNode $containerNode
     * @return Container
     *
     */
    public function initContainer($plugins, $containerNode)
    {
        $buffer = new Buffer();
        $containerNode->compile($buffer);

        $z = null;
        eval($buffer->getResult());

        foreach ($plugins as $plugin) {
            $plugin->setContainer($z);
        }

        return $z;
    }


    /**
     * Loads the configuration based on the specified file name convention. and plugin dir location
     *
     * @param string $fileName
     * @param string $pluginDir
     * @return array
     * @throws \UnexpectedValueException
     */
    public function getConfig($fileName, $pluginDir)
    {
        if (!$fileName) {
            $fileName = 'z.yml';
        }
        if ($pluginDir) {
            $pluginDirs = explode(PATH_SEPARATOR, $pluginDir);
        } else {
            $pluginDirs = array();
        }
        $pluginDirs[]= __DIR__ . '/Resources/plugins';
        $pluginDirs[]= getcwd();

        $zFileLocator  = new FileLocator(array(getcwd(), getenv('HOME') . '/.config/z/'));
        $pluginLocator = new FileLocator($pluginDirs);

        $loader = new FileLoader($pluginLocator);

        try {
            $zfiles = $zFileLocator->locate($fileName, null, false);
        } catch (\InvalidArgumentException $e) {
            $zfiles = array();
        }
        foreach ($zfiles as $file) {
            $loader->load($file);
        }

        $pluginFiles = $loader->getPlugins();
        $plugins     = array();
        foreach ($pluginFiles as $name => $file) {
            require_once $file;
            $className = sprintf('Zicht\Tool\Plugin\%s\Plugin', ucfirst(basename($name)));
            $class     = new \ReflectionClass($className);
            if (!$class->implementsInterface('Zicht\Tool\PluginInterface')) {
                throw new \UnexpectedValueException("The class $className is not a 'Zicht\\Tool\\PluginInterface'");
            }
            $plugins[$name] = $class->newInstance();
        }

        $processor = new Processor();
        $config = $processor->processConfiguration(
            new Configuration($plugins),
            $loader->getConfigs()
        );

        return array($plugins, $config);
    }
}