<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Plugin\Svn;

use \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

use \Zicht\Tool\Plugin as BasePlugin;

/**
 * SVN plugin configuration
 */
class Plugin extends BasePlugin
{
    /**
     * Appends SVN configuration options
     *
     * @param \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $rootNode
     * @return mixed|void
     */
    public function appendConfiguration(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('vcs')
                    ->children()
                        ->scalarNode('url')->isRequired()->end()
                        ->scalarNode('version')->isRequired()->end()
                        ->arrayNode('export')
                            ->children()
                                ->scalarNode('revfile')->isRequired()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}