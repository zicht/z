<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool;

use \Symfony\Component\Config\Loader\FileLoader as BaseFileLoader;
use \Symfony\Component\Yaml\Yaml;

/**
 * The Z file loader
 */
class FileLoader extends BaseFileLoader
{
    /**
     * Identifies a plugin type configuration
     */
    const PLUGIN = 'plugin';

    protected $configs = array();
    protected $plugins = array();


    /**
     * @{inheritDoc}
     */
    public function load($resource, $type = null)
    {
        $config = Yaml::parse($resource);

        if (isset($config['plugins'])) {
            $this->processPlugins($config['plugins'], dirname($resource));
            unset($config['plugins']);
        }
        if (isset($config['imports'])) {
            $this->processImports($config['imports'], dirname($resource));
            unset($config['imports']);
        }

        $this->configs[]= $config;

        return $config;
    }


    /**
     * @{inheritDoc}
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'yml' === pathinfo($resource, PATHINFO_EXTENSION);
    }


    /**
     * Processes plugin definitions
     *
     * @param array $plugins
     * @return void
     */
    protected function processPlugins($plugins)
    {
        foreach ($plugins as $plugin) {
            try {
                $this->plugins[$plugin] = $this->getLocator()->locate($plugin . '/Plugin.php');
            } catch (\InvalidArgumentException $e) {
            }

            $this->import($this->getLocator()->locate($plugin . '/z.yml'), self::PLUGIN);
        }
    }


    /**
     * Processes imports
     *
     * @param array $imports
     * @param string $dir
     * @return void
     */
    protected function processImports($imports, $dir)
    {
        foreach ($imports as $import) {
            $this->setCurrentDir($dir);
            $this->configs[]= $this->import($import);
        }
    }


    /**
     * Returns all loaded configs
     *
     * @return array
     */
    public function getConfigs()
    {
        return $this->configs;
    }


    /**
     * Returns all loaded plugins
     *
     * @return array
     */
    public function getPlugins()
    {
        return $this->plugins;
    }
}