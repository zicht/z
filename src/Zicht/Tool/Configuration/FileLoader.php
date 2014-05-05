<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Configuration;

use \Symfony\Component\Config\Loader\FileLoader as BaseFileLoader;
use \Zicht\Version\Constraint;
use \Zicht\Version\Version;
use \Symfony\Component\Yaml\Yaml;
use \Zicht\Tool;


/**
 * The Z file loader
 */
class FileLoader extends BaseFileLoader
{
    /**
     * Identifies a plugin type configuration
     */
    const PLUGIN = 'plugin';

    /**
     * Contains all loaded config trees.
     *
     * @var array
     */
    protected $configs = array();

    /**
     * Contains all loaded plugin config trees.
     *
     * @var array
     */
    protected $plugins = array();


    /**
     * @{inheritDoc}
     */
    public function load($resource, $type = null)
    {
        $fileContents = file_get_contents($resource);
        $annotations = $this->parseAnnotations($fileContents);

        if (empty($annotations['version'])) {
            trigger_error("$resource does not contain a version annotation.", E_USER_NOTICE);
        } else {
            $failures = array();
            $coreVersion = Version::fromString(Tool\Version::CORE_VERSION);
            if (!Constraint::isMatch($annotations['version'], $coreVersion, $failures)) {
                trigger_error(
                    "Core version '{$coreVersion}' does not match version annotation '{$annotations['version']}'\n"
                    . "(specified in $resource; " . join("; ", $failures) . ")",
                    E_USER_WARNING
                );
            }
        }

        $config = Yaml::parse($fileContents);

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


    public function parseAnnotations($fileContents)
    {
        $ret = array();
        if (preg_match('/^#\s*@(\w+)[:=]?\s+([\'"])?(.*)\2\s*$/m', $fileContents, $m)) {
            $ret[$m[1]] = $m[3];
        }
        return $ret;
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
     * @param string $dir
     * @return void
     */
    protected function processPlugins($plugins, $dir)
    {
        foreach ($plugins as $name) {
            $this->addPlugin($name, $dir);
        }
    }

    /**
     * Adds a plugin to the config and load it.
     *
     * @param string $plugin
     * @param string $dir
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function addPlugin($plugin, $dir)
    {
        $hasPlugin = $hasZfile = false;
        try {
            $this->plugins[$plugin] = $this->getLocator()->locate($plugin . '/Plugin.php', $dir, true);
            $hasPlugin = true;
        } catch (\InvalidArgumentException $e) {
        }

        try {
            $this->import($this->getLocator()->locate($plugin . '/z.yml', $dir), self::PLUGIN);
            $hasZfile = true;
        } catch (\InvalidArgumentException $e) {
        }

        if (!$hasPlugin && !$hasZfile) {
            throw new \InvalidArgumentException(
                "Error loading plugin '{$plugin}'. Did you configure ZPLUGINPATH?"
            );
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