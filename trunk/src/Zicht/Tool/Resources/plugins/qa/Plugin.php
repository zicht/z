<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Plugin\Qa;

use \Zicht\Tool\Plugin as BasePlugin;
use \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * QA plugin
 */
class Plugin extends BasePlugin
{
    /**
     * Appends the QA configuration
     *
     * @param \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $rootNode
     * @return void
     */
    public function appendConfiguration(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('qa')
                    ->children()
                        ->arrayNode('phpcs')
                        ->children()
                        ->arrayNode('dir')
                        ->prototype('scalar')->end()
                        ->performNoDeepMerging()
                        ->end()
                        ->scalarNode('standard')->end()
                        ->scalarNode('options')->end()
                        ->end()
                        ->end()
                        ->arrayNode('jshint')
                        ->children()
                        ->arrayNode('files')
                        ->beforeNormalization()
                        ->ifString()->then(
                        function($v) {
                            return array_filter(array($v));
                        }
                    )
                        ->end()
                        ->prototype('scalar')->end()
                        ->performNoDeepMerging()
                        ->end()
                        ->scalarNode('run')->end()
                        ->end()
                    ->end()
                ->end()
        ->end();
    }
}