<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Plugin\Symfony;

use \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

use \Zicht\Tool\Plugin as BasePlugin;

/**
 * Provides the configuration for the symfony tasks
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
                ->arrayNode('symfony')
                    ->children()
                        ->scalarNode('console')->defaultValue('app/console')->end()
                        ->scalarNode('web')->defaultValue('web')->end()
                        ->booleanNode('assetic')->defaultValue(true)->end()
                        ->booleanNode('assets')->defaultValue(true)->end()
                        ->booleanNode('flush_cache')->defaultValue(true)->end()
                    ->end()
                    ->addDefaultsIfNotSet()
                ->end()
            ->end()
        ;
    }
}