<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */
namespace Zicht\Tool\Configuration;

use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Definition\Processor;
use Zicht\Tool\Debug;
use Zicht\Version\Version;


/**
 * Z-file configuration loader.
 */
class ConfigurationLoader
{
    /**
     * Create the configuration loader based the current shell environment variables.
     *
     * @param string $configFilename
     * @param Version $version
     * @return ConfigurationLoader
     * @codeCoverageIgnore
     */
    public static function fromEnv($configFilename, Version $version)
    {
        if (null === $configFilename) {
            $configFilename = getenv('ZFILE') ? getenv('ZFILE') : 'z.yml';
        }

        return new self(
            $configFilename,
            new PathDefaultFileLocator('ZPATH', array(getcwd(), getenv('HOME') . '/.config/z')),
            new FileLoader(
                new PathDefaultFileLocator(
                    'ZPLUGINPATH',
                    array(ZPREFIX . '/vendor/zicht/z-plugins/', getcwd())
                ),
                $version
            )
        );
    }

    protected $sourceFiles = array();
    protected $plugins = array();
    protected $configFilename = '';
    protected $configLocator = '';
    protected $loader = '';



    /**
     * Construct the loader.
     *
     * @param string $configFilename
     * @param \Symfony\Component\Config\FileLocatorInterface $configLocator
     * @param FileLoader $loader
     */
    public function __construct($configFilename, FileLocatorInterface $configLocator, FileLoader $loader)
    {
        $this->configFilename = $configFilename;
        $this->configLocator = $configLocator;
        $this->loader = $loader;
    }

    /**
     * Add a plugin on-the-fly
     *
     * @param string $name
     * @return void
     */
    public function addPlugin($name)
    {
        $this->loader->addPlugin($name, getcwd());
    }


    /**
     * Processes the configuration contents
     *
     * @return array
     *
     * @throws \UnexpectedValueException
     */
    public function processConfiguration()
    {
        Debug::enterScope('config');
        Debug::enterScope('load');
        try {
            $zfiles = $this->configLocator->locate($this->configFilename, null, false);
        } catch (\InvalidArgumentException $e) {
            $zfiles = array();
        }
        foreach ($zfiles as $file) {
            Debug::enterScope($file);
            $this->sourceFiles[]= $file;
            $this->loader->load($file);
            Debug::exitScope($file);
        }
        foreach ($this->loader->getPlugins() as $name => $file) {
            Debug::enterScope($file);
            $this->sourceFiles[]= $file;
            $this->loadPlugin($name, $file);
            Debug::exitScope($file);
        }
        Debug::exitScope('load');

        Debug::enterScope('process');
        $processor = new Processor();
        $ret = $processor->processConfiguration(
            new Configuration($this->plugins),
            $this->loader->getConfigs()
        );
        Debug::exitScope('process');
        Debug::exitScope('config');
        return $ret;
    }


    /**
     * Load the specified plugin instance.
     *
     * @param string $name
     * @param string $file
     * @return void
     *
     * @throws \UnexpectedValueException
     */
    protected function loadPlugin($name, $file)
    {
        require_once $file;
        $className = sprintf('Zicht\Tool\Plugin\%s\Plugin', ucfirst(basename($name)));
        $class     = new \ReflectionClass($className);
        if (!$class->implementsInterface('Zicht\Tool\PluginInterface')) {
            throw new \UnexpectedValueException("The class $className is not a 'Zicht\\Tool\\PluginInterface'");
        }
        $this->plugins[$name] = $class->newInstance();
    }


    /**
     * Returns all plugins registered while loading.
     *
     * @return array
     */
    public function getPlugins()
    {
        return $this->plugins;
    }

    /**
     * @return array
     */
    public function getSourceFiles()
    {
        return $this->loader->getSourceFiles();
    }
}
