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
     * @var \Zicht\Tool\PluginInterface[]
     */
    protected $plugins = array();

    /**
     * Construct the configuration with a set of plugins
     *
     * @param \Zicht\Tool\PluginInterface[] $plugins
     */
    public function __construct($plugins)
    {
        $this->plugins = $plugins;
    }


    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $zConfig = $treeBuilder->root('z');
        $zConfig
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
                            ->scalarNode('help')->defaultValue('No help available for this task')->end()
                            ->arrayNode('set')
                                ->prototype('scalar')
                                ->end()
                                ->useAttributeAsKey('name')
                                ->defaultValue(array())
                            ->end()
                            ->scalarNode('unless')->end()
                            ->arrayNode('pre')
                                ->beforeNormalization()
                                    ->ifString()->then(
                                        function($s) {
                                            return array($s);
                                        }
                                    )
                                ->end()
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
                            ->scalarNode('ssh')->end()
                            ->scalarNode('root')->end()
                            ->scalarNode('web')->end()
                            ->scalarNode('url')->end()
                            ->scalarNode('db')->end()
                        ->end()
                    ->end()
                    ->useAttributeAsKey('name')
                ->end()
                ->arrayNode('build')
                    ->children()
                        ->scalarNode('dir')->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        foreach ($this->plugins as $plugin) {
            $plugin->appendConfiguration($zConfig);
        }

        return $treeBuilder;
    }
}