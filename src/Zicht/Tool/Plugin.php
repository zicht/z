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
    // @codeCoverageIgnoreStart
    /**
     * @{inheritDoc}
     */
    public function appendConfiguration(ArrayNodeDefinition $rootNode)
    {
    }


    /**
     * @{inheritDoc}
     */
    public function setContainer(Container\Container $container)
    {
    }
    // @codeCoverageIgnoreEnd
}