<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Configuration;

use \Symfony\Component\Config\Loader\FileLoader as BaseFileLoader;
use \Zicht\Version\Version;
use \Zicht\Tool;
use \Zicht\Version\Constraint;
use \Symfony\Component\Yaml;

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
    protected $pluginPaths  = array();


    /**
     * @{inheritDoc}
     */
    public function load($resource, $type = null)
    {
        if (!is_file($resource)) {
            $fileContents = $resource;
        } else {
            $fileContents = file_get_contents($resource);
        }
        $annotations = $this->parseAnnotations($fileContents);

        if (!empty($annotations['version'])) {
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

        try {
            $config = Yaml\Yaml::parse($fileContents);
        } catch (Yaml\Exception\ExceptionInterface $e) {
            throw new \RuntimeException("YAML error in {$resource}", 0, $e);
        }

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
     * Parse the annotations contained in commented lines, starting with #
     *
     * Annotation format is '@' followed by a word, followed by an optional ':' or '=', followed by a quoted value,
     * e.g.
     * <code>@foo="bar"</code>
     *
     * @param string $fileContents
     * @return array
     */
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
        foreach ($plugins as $plugin) {
            $this->addPlugin($plugin, $dir);
        }
    }

    public function addPlugin($plugin, $dir)
    {
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