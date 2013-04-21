<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */
namespace Zicht\Tool\Configuration;

use Symfony\Component\Config\FileLocatorInterface;
use \Symfony\Component\Config\Definition\Processor;

class ConfigurationLoader
{
    /**
     * @param string $configFilename
     * @return ConfigurationLoader
     * @codeCoverageIgnore
     */
    public static function fromEnv($configFilename = 'z.yml')
    {
        return new self(
            getenv('ZFILE') ? getenv('ZFILE') : $configFilename,
            new PathDefaultFileLocator('ZPATH', array(getcwd(), getenv('HOME') . '/.config/z')),
            new FileLoader(PathDefaultFileLocator('ZPLUGINPATH', array(ZPREFIX . '/vendor/zicht/z-plugins/', getcwd())))
        );
    }



    function __construct($configFilename, FileLocatorInterface $configLocator, FileLoader $loader)
    {
        $this->configFilename = $configFilename;
        $this->loader = $loader;
        $this->configLocator = $configLocator;
        $this->plugins = array();
    }


    function processConfiguration()
    {
        try {
            $zfiles = $this->configLocator->locate($this->configFilename, null, false);
        } catch (\InvalidArgumentException $e) {
            $zfiles = array();
        }
        foreach ($zfiles as $file) {
            $this->loader->load($file);
        }

        $this->plugins = array();
        foreach ($this->loader->getPlugins() as $name => $file) {
            require_once $file;
            $className = sprintf('Zicht\Tool\Plugin\%s\Plugin', ucfirst(basename($name)));
            $class     = new \ReflectionClass($className);
            if (!$class->implementsInterface('Zicht\Tool\PluginInterface')) {
                throw new \UnexpectedValueException("The class $className is not a 'Zicht\\Tool\\PluginInterface'");
            }
            $this->plugins[$name] = $class->newInstance();
        }

        $processor = new Processor();
        return $processor->processConfiguration(
            new Configuration($this->plugins),
            $this->loader->getConfigs()
        );
    }



    function getPlugins()
    {
        return $this->plugins;
    }
}