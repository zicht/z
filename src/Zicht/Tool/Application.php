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
use \Symfony\Component\Console\Input\ArgvInput;

use \Zicht\Tool\Command as Cmd;
use \Zicht\Tool\Command\TaskCommand;
use \Zicht\Tool\Container\Configuration;
use \Zicht\Tool\Container\Container;
use \Zicht\Tool\Container\Flattener;
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
    }

    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        return parent::run($input, new \Zicht\Tool\Output\ConsoleOutput());
    }


    public function doRun(InputInterface $input, OutputInterface $output)
    {
        if (true === $input->hasParameterOption(array('--quiet', '-q'))) {
            $output->setVerbosity(OutputInterface::VERBOSITY_QUIET);
        } elseif (true === $input->hasParameterOption(array('--verbose', '-v'))) {
            $output->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
        }

        $container = $this->initContainer(
            $input->hasParameterOption(array('--verbose', '-v')),
            $input->hasParameterOption(array('--force', '-f')),
            $input->hasParameterOption(array('--explain')),
            getenv('ZFILE'),
            getenv('ZPLUGINDIR')
        );

        $container->output = $output;

        $this->add(new Cmd\DumpCommand($container));

        /** @var $task \Zicht\Tool\Container\Task */
        foreach ($this->tasks as $name => $task) {
            // if a tasks is prefixed with an underscore, it is considered an internal task
            if (substr($name, 0, 1) !== '_') {
                $cmd = new TaskCommand($container, str_replace('.', ':', $name));
                foreach ($task->getArguments() as $var => $isRequired) {
                    $cmd->addArgument($var, $isRequired ? InputArgument::REQUIRED : InputArgument::OPTIONAL);
                }
                $cmd->addOption('explain', '', InputOption::VALUE_NONE, 'Explains the commands that are executed without executing them.');
                $cmd->addOption('force', 'f', InputOption::VALUE_NONE, 'Force execution of otherwise skipped tasks.');
                $cmd->setHelp($task->getHelp());
                $cmd->setDescription(preg_replace('/^([^\n]*).*/s', '$1', $task->getHelp()));
                $this->add($cmd);
            }
        }
        $container->console_dialog_helper = $this->getHelperSet()->get('dialog');

        return parent::doRun($input, $output);
    }


    /**
     * Initializes the container.
     *
     * @return Container
     *
     * @throws \UnexpectedValueException
     */
    public function initContainer($verbose, $force, $explain, $fileName, $pluginDir)
    {
        list($plugins, $config) = $this->getConfig($fileName, $pluginDir);

        $config += compact('verbose', 'force', 'explain');

        $z = new Container($config);

        $buffer = new \Zicht\Tool\Script\Buffer();
        $this->tasks = array();
        foreach ($config['tasks'] as $name => $taskDef) {
            $task = new Task($taskDef, $name);
            $this->tasks[$name]= $task;
            $buffer->indent(1)->writeln('$z->decl(');
            $buffer->writeln(var_export(array_merge(array('tasks'), explode('.', $name)), true) . ',');
            $task->compile($buffer);
            $buffer->indent(-1)->writeln(');');
        }
        unset($config['tasks']);

        $z->definition = $buffer->getResult();

        eval($buffer->getResult());

        foreach ($plugins as $plugin) {
            $plugin->setContainer($z);
        }

        return $z;
    }

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