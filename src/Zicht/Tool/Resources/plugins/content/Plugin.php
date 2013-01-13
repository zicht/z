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
     * Appends the content configuration
     *
     * @param \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $rootNode
     * @return mixed|void
     */
    public function appendConfiguration(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('content')
                    ->children()
                        ->arrayNode('dir')
                            ->prototype('scalar')->end()
                        ->performNoDeepMerging()
                    ->end()
                ->end()
            ->end()
        ;
    }
}