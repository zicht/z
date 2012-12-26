<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Tool\Container;

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
                            ->arrayNode('set')
                                ->prototype('scalar')->end()
                                ->defaultValue(array())
                            ->end()
                            ->arrayNode('pre')
                                ->performNoDeepMerging()
                                ->prototype('scalar')->end()
                                ->defaultValue(array())
                            ->end()
                            ->arrayNode('post')
                                ->performNoDeepMerging()
                                ->prototype('scalar')->end()
                                ->defaultValue(array())
                            ->end()
                            ->arrayNode('do')
                                ->beforeNormalization()
                                    ->ifString()->then(function($s) { return array($s); })
                                 ->end()
                                ->performNoDeepMerging()
                                ->prototype('scalar')->end()
                                ->defaultValue(array())
                            ->end()
                            ->scalarNode('yield')->defaultValue(null)->end()
                        ->end()
                    ->end()
                    ->useAttributeAsKey('name')
                ->end()
                ->arrayNode('env')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('ssh')->isRequired()->end()
                            ->scalarNode('root')->isRequired()->end()
                            ->scalarNode('web')->isRequired()->end()
                            ->scalarNode('url')->isRequired()->end()
                            ->scalarNode('db')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('vcs')
                    ->children()
                        ->scalarNode('url')->isRequired()->end()
                        ->scalarNode('version')->isRequired()->end()
                    ->end()
                ->end()
                ->arrayNode('build')
                    ->children()
                        ->scalarNode('dir')->end()
                        ->scalarNode('version')->end()
                    ->end()
                ->end()
                ->arrayNode('sync')
                    ->children()
                        ->scalarNode('options')->end()
                    ->end()
                ->end()
                ->arrayNode('content')
                    ->children()
                        ->arrayNode('dir')->prototype('scalar')->end()->end()
                    ->end()
                ->end()
                ->arrayNode('qa')
                    ->children()
                        ->scalarNode('standard')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

}