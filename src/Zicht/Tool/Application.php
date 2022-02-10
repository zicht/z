<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Tool;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Output\OutputInterface;
use Zicht\Version\Version;
use Zicht\Tool\Command as Cmd;
use Zicht\Tool\Configuration\ConfigurationLoader;
use Zicht\Tool\Container\Container;
use Zicht\Tool\Container\ContainerCompiler;
use Zicht\Tool\Container\VerboseException;

/**
 * Z CLI Application
 */
class Application extends BaseApplication
{
    public static $HEADER = <<<EOSTR
.------------.
|    ____    |
|   |__  |   |
|     / /    |
|    / /_    |
|   |____|   |
|   ------   |
'------------'
EOSTR;

    protected $container = null;
    protected $loader = null;
    protected $plugins = array();


    /**
     * Construct the application with the specified name, version and config loader.
     *
     * @param string $name
     * @param Version $version
     * @param Configuration\ConfigurationLoader $loader
     */
    public function __construct($name = null, Version $version = null, ConfigurationLoader $loader = null)
    {
        parent::__construct($name, (string)$version);
        $this->setDefaultCommand('z:list');
        $this->loader = $loader;
    }


    /**
     * Custom exception rendering, renders only the exception types and messages, hierarchically, but with regular
     * formatting if verbosity is higher.
     */
    public function renderException(\Exception $e, OutputInterface $output)
    {
        if ($output->getVerbosity() > OutputInterface::VERBOSITY_VERBOSE) {
            parent::renderException($e, $output);
        } else {
            /** @var $ancestry \Exception[] */
            $ancestry = array();
            $maxLength = 0;
            do {
                $ancestry[] = $e;
                $maxLength = max($maxLength, strlen(get_class($e)));
                $last = $e;
            } while ($e = $e->getPrevious());

            if ($last instanceof VerboseException) {
                $last->output($output);
            } else {
                $depth = 0;
                foreach ($ancestry as $e) {
                    $output->writeln(
                        sprintf(
                            '%s%-40s %s',
                            ($depth > 0 ? str_repeat('   ', $depth - 1) . '-> ' : ''),
                            '<fg=red;options=bold>' . $e->getMessage() . '</fg=red;options=bold>',
                            $depth == count($ancestry) - 1 ? str_pad('[' . get_class($e) . ']', $maxLength + 15, ' ') : ''
                        )
                    );
                    $depth++;
                }
            }
        }
        $output->writeln('[' . join('::', Debug::$scope) . ']');
    }


    /**
     * Set the container instance
     *
     * @param Container $container
     * @return void
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param array $config
     * @return string
     */
    protected function getCacheKey(array $config)
    {
        $seed = json_encode($this->loader->getSourceFiles());
        foreach ($this->loader->getPlugins() as $name => $plugin) {
            if ($plugin instanceof PluginCacheSeedInterface) {
                $seed .= $plugin->getCacheSeed($config);
            }
        }
        return sha1($seed);
    }


    /**
     * Returns the container instance, and initializes it if not yet available.
     *
     * @param bool $forceRecompile
     * @return Container
     */
    public function getContainer($forceRecompile = false)
    {
        if (null === $this->container) {
            $config = $this->loader->processConfiguration();
            $config['z']['sources'] = $this->loader->getSourceFiles();
            $config['z']['cache_file'] = sys_get_temp_dir() . '/z_' . $this->getCacheKey($config) . '.php';
            if ($forceRecompile && is_file($config['z']['cache_file'])) {
                unlink($config['z']['cache_file']);
                clearstatcache();
            }
            $compiler = new ContainerCompiler(
                $config,
                $this->loader->getPlugins(),
                $config['z']['cache_file']
            );
            $this->container = $compiler->getContainer();
        }
        return $this->container;
    }


    protected function getDefaultCommands()
    {
        return array(
            new Cmd\ListCommand(),
            new Cmd\HelpCommand(),
            new Cmd\EvalCommand(),
            new Cmd\DumpCommand(),
            new Cmd\InfoCommand()
        );
    }

    protected function getDefaultInputDefinition()
    {
        return new InputDefinition(
            array(
                new InputArgument('command', InputArgument::REQUIRED, 'The command to execute'),

                new InputOption('--help', '-h', InputOption::VALUE_NONE, 'Display this help message.'),
                new InputOption('--verbose', '-v|vv|vvv', InputOption::VALUE_NONE, 'Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug'),
                new InputOption('--no-cache', '-c', InputOption::VALUE_NONE, 'Force recompilation of container code'),
                new InputOption('--version', '-V', InputOption::VALUE_NONE, 'Display this application version.'),
                new InputOption('--no-ansi', '',  InputOption::VALUE_NONE, 'Disable ANSI output'),
            )
        );
    }


    /**
     * {@inheritDoc}
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $debug = $input->hasParameterOption(array('--debug'));

        if (!$debug) {
            set_error_handler(new ErrorHandler($input, $output), E_USER_WARNING | E_USER_NOTICE | E_USER_DEPRECATED | E_RECOVERABLE_ERROR);
        }

        $output->setFormatter(new Output\PrefixFormatter($output->getFormatter()));

        if (true === $input->hasParameterOption(array('--quiet', '-q'))) {
            $output->setVerbosity(OutputInterface::VERBOSITY_QUIET);
        } elseif (true === $input->hasParameterOption(array('-vvv'))) {
            $output->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
        } elseif (true === $input->hasParameterOption(array('-vv'))) {
            $output->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE);
        } elseif (true === $input->hasParameterOption(array('--verbose', '-v'))) {
            $output->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
        }

        $this->plugins = array();

        if ($input->hasParameterOption('--plugin')) {
            $value = array_filter(array_map('trim', explode(',', $input->getParameterOption('--plugin'))));

            foreach ($value as $name) {
                $this->loader->addPlugin($name);
            }
        }

        Debug::enterScope('init');
        $container = $this->getContainer($input->hasParameterOption(array('--no-cache', '-c')));

        $container->output = $output;

        $container->set('DEBUG', $debug);
        $container->set('VERBOSE', $input->hasParameterOption(array('--verbose', '-v')));
        $container->set('FORCE', $input->hasParameterOption(array('--force', '-f')));
        $container->set('EXPLAIN', $input->hasParameterOption(array('--explain')));

        foreach ($container->getCommands() as $task) {
            $this->add($task);
        }

        Debug::exitScope('init');

        Debug::enterScope('run');
        if (true === $input->hasParameterOption(array('--help', '-h'))) {
            if (!$this->getCommandName($input)) {
                $input = new ArrayInput(array('command' => 'z:list'));
            }
        }

        $ret = parent::doRun($input, $output);
        Debug::exitScope('run');

        return $ret;
    }

    /**
     * {@inheritDoc}
     */
    public function getHelp()
    {
        $ret = parent::getHelp();
        if (self::$HEADER) {
            $ret = self::$HEADER . PHP_EOL . PHP_EOL . $ret;
        }
        return $ret;
    }

    public function get($name)
    {
        // The 'help' name can not be overridden as it's hard coded in the base class.
        if ('help' === $name) {
            $name = 'z:help';
        }

        return parent::get($name);
    }
}
