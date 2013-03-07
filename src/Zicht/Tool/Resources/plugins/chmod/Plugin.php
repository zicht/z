<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Plugin\Chmod;

use \Zicht\Tool\Plugin as BasePlugin;
use \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * Content plugin
 */
class Plugin extends BasePlugin
{
    /**
     * @{inheritDoc}
     */
    public function appendConfiguration(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('chmod')
                    ->children()
                        ->arrayNode('defaults')
                            ->children()
                                ->scalarNode('dir')->end()
                                ->scalarNode('file')->end()
                            ->end()
                        ->end()
                        ->arrayNode('writable')->prototype('scalar')->end()->end()
                        ->arrayNode('executable')->prototype('scalar')->end()->end()
                    ->end()
                ->end()
        ;
    }
}