<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */
namespace Zicht\Tool\Configuration;

use Symfony\Component\Config\FileLocator;

/**
 * A FileLocator implementation that uses an environment PATH variable, and defaults to other paths if that
 * environment variable does not exist
 */
class PathDefaultFileLocator extends FileLocator
{
    /**
     * Expand all path elements with globbing.
     *
     * @param string $paths
     * @return array
     */
    private static function expand($paths)
    {
        return array_filter(array_reduce(array_map('glob', $paths), 'array_merge', []));
    }


    /**
     * Construct the locator based on the passed environment variable.
     *
     * @param array|string $envName
     * @param array $defaultPaths
     */
    public function __construct($envName, $defaultPaths = array())
    {
        parent::__construct(self::expand(getenv($envName) ? explode(PATH_SEPARATOR, getenv($envName)) : $defaultPaths));
    }
}
