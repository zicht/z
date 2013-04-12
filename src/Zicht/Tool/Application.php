<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool;

use \Symfony\Component\Console\Application as BaseApplication;
use \Symfony\Component\Yaml\Yaml;
use \Symfony\Component\Config\FileLocator;
use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

use \Zicht\Tool\Command as Cmd;
use Zicht\Tool\Configuration\ConfigurationLoader;
use \Zicht\Tool\Container\Container;
use \Zicht\Tool\Container\ContainerCompiler;

/**
 * Z CLI Application
 */
class Application extends BaseApplication
{
    public static $HEADER = <<<EOSTR
  ___
 /_ /
  //
 //_
/__/
EOSTR;

    protected $container = null;


    public function __construct($name, $version, ConfigurationLoader $loader = null)
    {
        parent::__construct($name, $version);
        $this->loader = $loader;
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
        $output = (null !== $output ? $output : new Output\ConsoleOutput());

        /**
         * Emits deprecation warnings to stderr.
         *
         * @param int $err
         * @param string $errstr
         */
        set_error_handler(function($err, $errstr) use($output) {
            static $repeating = array();
            if (in_array($errstr, $repeating)) {
                return;
            }
            $repeating[]= $errstr;
            $output->writeln("[DEPRECATED] $errstr\n");
        }, E_USER_DEPRECATED);

        return parent::run($input, $output);
    }

    /**
     * @param \Exception $e
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function renderException($e, $output)
    {
        if ($output->getVerbosity() == OutputInterface::VERBOSITY_NORMAL) {
            /** @var $ancestry \Exception[] */
            $ancestry = array();
            $maxLength = 0;
            do {
                $ancestry[] = $e;
                $maxLength = max($maxLength, strlen(get_class($e)));
            } while ($e = $e->getPrevious());

            $depth = 0;
            foreach ($ancestry as $e) {
                $output->writeln(
                    sprintf(
                        '%s %s%s',
                        str_pad('<error>' . get_class($e) . '</error>', $maxLength + 15, ' '),
                        ($depth > 0 ? str_repeat('   ', $depth - 1) . '-> ' : ''),
                        $e->getMessage()
                    )
                );
                $depth ++;
            }
        } else {
            parent::renderException($e, $output);
        }
    }


    public function setContainer(Container $container)
    {
        $this->container = $container;
    }


    public function getContainer()
    {
        if (null === $this->container) {
            $config = $this->loader->processConfiguration();
            $compiler = new ContainerCompiler($config, '.z.php');
            $this->container = $compiler->getContainer();
            foreach ($this->loader->getPlugins() as $plugin) {
                $plugin->setContainer($this->container);
            }
        }
        return $this->container;
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

        $container = $this->getContainer();
        $container->output = $output;

        $container->set('verbose',  $input->hasParameterOption(array('--verbose', '-v')));
        $container->set('force',    $input->hasParameterOption(array('--force', '-f')));
        $container->set('explain',  $input->hasParameterOption(array('--explain')));

        foreach ($container->getCommands() as $task) {
            $this->add($task);
            $task->setContainer($container);
        }
        $container->console_dialog_helper = $this->getHelperSet()->get('dialog');

        return parent::doRun($input, $output);
    }


    /**
     * @{inheritDoc}
     */
    public function getHelp()
    {
        $ret = parent::getHelp();
//        if (self::$HEADER) {
//            $ret = self::$HEADER . PHP_EOL . PHP_EOL . $ret;
//        }
        return $ret;
    }
}