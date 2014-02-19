<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Config\Exception\FileLoaderImportCircularReferenceException;
use Symfony\Component\Config\Exception\FileLoaderLoadException;
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

    protected $configs      = array();
    protected $plugins      = array();
    protected $pluginPaths  = array();


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
    protected function processPlugins($plugins, $dir)
    {
        foreach ($plugins as $plugin) {
            $hasPlugin = $hasZfile = false;
            try {
                $this->plugins[$plugin] = $this->getLocator()->locate($plugin . '/Plugin.php', $dir, true);
                $this->pluginPaths[$plugin] = dirname($this->plugins[$plugin]);
                $hasPlugin = true;
            } catch (\InvalidArgumentException $e) {
            }

            try {
                $zFileLocation = $this->getLocator()->locate($plugin . '/z.yml', $dir);
                $this->import($zFileLocation, self::PLUGIN);
                if (!isset($this->pluginPaths[$plugin])) {
                    $this->pluginPaths[$plugin] = dirname($zFileLocation);
                } else if ($this->pluginPaths[$plugin] != dirname($zFileLocation)) {
                    throw new \UnexpectedValueException(
                        "Ambiguous plugin configuration:\n"
                        . "There was a Plugin.php found in {$this->pluginPaths[$plugin]}, but also a z.yml at $zFileLocation"
                    );
                }
                $hasZfile = true;
            } catch (\InvalidArgumentException $e) {
            }

            if (!$hasPlugin && !$hasZfile) {
                throw new \InvalidArgumentException("You need at least either a z.yml or a Plugin.php in the plugin path for {$plugin}");
            }
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
            $this->import($import);
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


    /**
     * Returns all loaded paths
     *
     * @return array
     */
    public function getPluginPaths()
    {
        return $this->pluginPaths;
    }
}