<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Plugin\Content;

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
        $filter = function ($s) {
            return array_filter(array($s));
        };
        $rootNode
            ->children()
                ->arrayNode('content')
                    ->children()
                        ->arrayNode('dir')
                            ->prototype('scalar')->end()
                            ->performNoDeepMerging()
                        ->end()
                        ->arrayNode('db')
                            ->children()
                                ->arrayNode('structure')
                                    ->beforeNormalization()->ifString()->then($filter)->end()
                                    ->prototype('scalar')->end()
                                    ->performNoDeepMerging()
                                ->end()
                                ->arrayNode('full')
                                    ->beforeNormalization()->ifString()->then($filter)->end()
                                    ->prototype('scalar')->end()
                                    ->performNoDeepMerging()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}