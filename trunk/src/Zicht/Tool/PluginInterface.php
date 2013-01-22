<?php
/**
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool;

use \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * Interface for plugins
 */
interface PluginInterface
{
    /**
     * Appends the configuration to the node
     *
     * @param \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $rootNode
     * @return mixed
     */
    public function appendConfiguration(ArrayNodeDefinition $rootNode);


    /**
     * Set the container instance
     *
     * @param Container\Container $container
     * @return mixed
     */
    public function setContainer(Container\Container $container);


    /**
     * Initialize/alter the config
     *
     * @param array $config
     * @return mixed
     */
    public function init(array &$config);
}