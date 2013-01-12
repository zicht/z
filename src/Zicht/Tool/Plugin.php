<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool;

use \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * Base plugin class
 */
abstract class Plugin implements PluginInterface
{
    /**
     * Appends the configuration to the node
     *
     * @param \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $rootNode
     * @return mixed
     */
    public function appendConfiguration(ArrayNodeDefinition $rootNode)
    {
    }

    /**
     * Set the container instance
     *
     * @param Container\Container $container
     * @return mixed
     */
    public function setContainer(Container\Container $container)
    {
    }
}