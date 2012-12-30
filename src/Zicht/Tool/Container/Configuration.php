<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Tool\Container;

use \Symfony\Component\Config\Definition\ConfigurationInterface;
use \Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * Configuration implementation validation a Z file configuration
 */
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
                        ->beforeNormalization()
                            ->ifString()->then(
                                function($v) {
                                    return array('do' => $v);
                                }
                            )
                        ->end()
                        ->children()
                            ->scalarNode('name')->end()
                            ->arrayNode('set')
                                ->prototype('scalar')->end()
                                ->defaultValue(array())
                            ->end()
                            ->scalarNode('unless')->defaultNull()->end()
                            ->arrayNode('pre')
                                ->beforeNormalization()
                                    ->ifString()->then(
                                        function($s) {
                                            return array($s);
                                        }
                                    )
                                ->end()
                                ->performNoDeepMerging()
                                ->prototype('scalar')->end()
                                ->defaultValue(array())
                            ->end()
                            ->arrayNode('post')
                                ->beforeNormalization()
                                    ->ifString()->then(
                                        function($s) {
                                            return array($s);
                                        }
                                    )
                                ->end()
                                ->performNoDeepMerging()
                                ->prototype('scalar')->end()
                                ->defaultValue(array())
                            ->end()
                            ->arrayNode('do')
                                ->beforeNormalization()
                                    ->ifString()->then(
                                        function($s) {
                                            return array($s);
                                        }
                                    )
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
                            ->scalarNode('web')->defaultValue('')->end()
                            ->scalarNode('url')->isRequired()->end()
                            ->scalarNode('db')->end()
                        ->end()
                    ->end()
                    ->useAttributeAsKey('name')
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
                        ->scalarNode('exclude_file')->end()
                    ->end()
                ->end()
                ->arrayNode('content')
                    ->children()
                        ->arrayNode('dir')->prototype('scalar')->end()->end()
                    ->end()
                ->end()
                ->arrayNode('qa')
                    ->children()
                        ->arrayNode('phpcs')
                            ->children()
                                ->arrayNode('dir')
                                    ->prototype('scalar')->end()
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
                                ->end()
                                ->scalarNode('run')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}