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
        $toArray = function ($s) {
            return array($s);
        };
        $zConfig
            ->children()
                ->arrayNode('tasks')
                    ->prototype('array')
                        ->beforeNormalization()
                            ->ifTrue(
                                function($in) {
                                    return
                                        is_string($in)

                                        // allow for 'lists' (skipping the 'do' key)
                                     || (is_array($in) && range(0, count($in) -1) === array_keys($in));
                                }
                            )
                            ->then(
                                function($v) {
                                    return array('do' => $v);
                                }
                            )
                        ->end()
                        ->children()
                            ->scalarNode('name')->end()
                            ->scalarNode('help')->defaultValue(null)->end()
                            ->arrayNode('set')
                                ->prototype('scalar')
                                ->end()
                                ->useAttributeAsKey('name')
                                ->defaultValue(array())
                            ->end()
                            ->scalarNode('unless')->defaultValue(null)->end()
                            ->scalarNode('assert')->defaultValue(null)->end()
                            ->arrayNode('pre')
                                ->beforeNormalization()
                                    ->ifString()->then($toArray)
                                ->end()
                                ->prototype('scalar')->end()
                                ->defaultValue(array())
                            ->end()
                            ->arrayNode('post')
                                ->beforeNormalization()
                                    ->ifString()->then($toArray)
                                ->end()
                                ->prototype('scalar')->end()
                                ->defaultValue(array())
                            ->end()
                            ->arrayNode('do')
                                ->beforeNormalization()
                                    ->ifString()->then($toArray)
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
                ->arrayNode('envs')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('ssh')->end()
                            ->scalarNode('root')
                                ->validate()
                                    ->ifTrue(
                                        function($v) {
                                            return preg_match('~[^/]$~', $v);
                                        }
                                    )
                                    ->then(
                                        function($v) {
                                            return $v . '/';
                                        }
                                    )
                                ->end()
                            ->end()
                            ->scalarNode('web')->end()
                            ->scalarNode('url')->end()
                            ->scalarNode('db')->end()
                        ->end()
                    ->end()
                    ->useAttributeAsKey('name')
                ->end()
            ->end()
        ->end();

        foreach ($this->plugins as $plugin) {
            $plugin->appendConfiguration($zConfig);
        }

        return $treeBuilder;
    }
}