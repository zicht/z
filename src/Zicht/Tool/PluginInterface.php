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
     * @return void
     */
    public function appendConfiguration(ArrayNodeDefinition $rootNode);


    /**
     * Set the container instance
     *
     * @param Container\Container $container
     * @return void
     */
    public function setContainer(Container\Container $container);


    /**
     * Set the containerbuilder class, so building logic can be added.
     *
     * @param Container\ContainerBuilder $builder
     * @return void
     */
    public function setContainerBuilder(Container\ContainerBuilder $builder);
}