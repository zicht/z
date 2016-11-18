<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Configuration;

use Symfony\Component\Config\Loader\FileLoader as BaseFileLoader;
use Symfony\Component\Config\FileLocatorInterface;
use Zicht\Tool\Parser;
use Zicht\Version\Version;
use Zicht\Tool;
use Zicht\Tool\Debug;
use Zicht\Version\Constraint;

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
    protected $sourceFiles = array();

    /**
     * Constructor.
     *
     * @param FileLocatorInterface $locator
     * @param \Zicht\Version\Version $version
     */
    public function __construct(FileLocatorInterface $locator, Version $version = null)
    {
        parent::__construct($locator);

        $this->version = $version;
    }

    /**
     * @param array $sourceFiles
     */
    public function setSourceFiles($sourceFiles)
    {
        $this->sourceFiles = $sourceFiles;
    }

    /**
     * @return array
     */
    public function getSourceFiles()
    {
        return array_unique($this->sourceFiles);
    }

    /**
     * @{inheritDoc}
     */
    public function load($resource, $type = null)
    {
        if (!is_file($resource)) {
            $resource = null;
            $fileContents = $resource;
        } else {
            $this->sourceFiles[]= $resource;
            $fileContents = file_get_contents($resource);
        }
        Debug::enterScope('annotations');
        $annotations = $this->parseAnnotations($fileContents);

        if ($this->version && !empty($annotations['version'])) {
            $failures = array();
            if (!Constraint::isMatch($annotations['version'], $this->version, $failures)) {
                trigger_error(
                    "Core version '{$this->version}' does not match version annotation '{$annotations['version']}'\n"
                    . "(specified in $resource; " . join("; ", $failures) . ")",
                    E_USER_WARNING
                );
            }
        }
        Debug::exitScope('annotations');

        $parser = new Parser($resource, $fileContents);
        $config = $parser->parse();

        if (isset($config['plugins'])) {
            Debug::enterScope('plugins');
            $this->processPlugins($config['plugins'], dirname($resource));
            Debug::exitScope('plugins');
            unset($config['plugins']);
        }
        if (isset($config['imports'])) {
            Debug::enterScope('imports');
            $this->processImports($config['imports'], dirname($resource));
            Debug::exitScope('imports');
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

    /**
     * Add a plugin at the passed location
     *
     * @param string $name
     * @param string $dir
     * @return void
     *
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public function addPlugin($name, $dir)
    {
        Debug::enterScope($name);
        $hasPlugin = $hasZfile = false;

        try {
            $this->plugins[$name] = $this->getLocator()->locate($name . '/Plugin.php', $dir, true);
            $this->pluginPaths[$name] = dirname($this->plugins[$name]);

            $hasPlugin = true;
            $this->sourceFiles[]= $this->plugins[$name];
        } catch (\InvalidArgumentException $e) {
        }

        try {
            $zFileLocation = $this->getLocator()->locate($name . '/z.yml', $dir);
            $this->import($zFileLocation, self::PLUGIN);
            if (!isset($this->pluginPaths[$name])) {
                $this->pluginPaths[$name] = dirname($zFileLocation);
            } else if ($this->pluginPaths[$name] != dirname($zFileLocation)) {
                throw new \UnexpectedValueException(
                    "Ambiguous plugin configuration:\n"
                    . "There was a Plugin.php found in {$this->pluginPaths[$name]}, but also a z.yml at $zFileLocation"
                );
            }

            $hasZfile = true;
            $this->sourceFiles[]= $zFileLocation;
        } catch (\InvalidArgumentException $e) {
        }

        if (!$hasPlugin && !$hasZfile) {
            throw new \InvalidArgumentException("You need at least either a z.yml or a Plugin.php in the plugin path for '{$name}'");
        }
        Debug::exitScope($name);
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
