<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Tool;

use \Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $tasks = $treeBuilder->root('z');
        $tasks
            ->children()
                ->arrayNode('tasks')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('name')->end()
                            ->arrayNode('depends')
                                ->performNoDeepMerging()
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('post')
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                    ->useAttributeAsKey('name')
                ->end()
                ->arrayNode('environments')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('ssh')->isRequired()->end()
                            ->scalarNode('root')->isRequired()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('versioning')
                    ->children()
                        ->scalarNode('url')->isRequired()->end()
                    ->end()
                ->end()
                ->arrayNode('options')
                    ->children()
                        ->arrayNode('build')
                            ->children()
                                ->scalarNode('dir')->end()
                                ->scalarNode('version')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

}