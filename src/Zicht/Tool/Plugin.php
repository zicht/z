<?php
/**
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright Zicht Online <http://zicht.nl>
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
     * @codeCoverageIgnore
     */
    public function appendConfiguration(ArrayNodeDefinition $rootNode)
    {
    }


    /**
     * Set the container instance
     *
     * @param Container\Container $container
     * @return mixed
     * @codeCoverageIgnore
     */
    public function setContainer(Container\Container $container)
    {
    }
}