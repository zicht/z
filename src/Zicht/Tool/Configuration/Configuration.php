<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Tool\Configuration;

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

        // to be removed in 1.2
        $replaceLegacyEnv = function ($config) {
            trigger_error(
                'As of version 1.1, the "env" configuration is deprecated and must be replaced by "envs".',
                E_USER_DEPRECATED
            );
            $config['envs'] = $config['env'];
            unset($config['env']);
            return $config;
        };
        // to be removed in 1.2
        $hasLegacyEnv = function ($config) {
            return isset($config['env']);
        };

        // to be removed in 1.2
        $replaceEnvWithTargetEnv = function ($set) {
            trigger_error(
                "As of version 1.1, Using 'env' as a set variable is deprecated. "
                    . "Please use 'target_env' in stead",
                E_USER_DEPRECATED
            );
            $repl = array();
            // this foreach is needed to maintain the internal sorting
            foreach ($set as $k => $v) {
                $repl[$k == 'env' ? 'target_env' : $k] = $v;
            }

            return $repl;
        };
        $zConfig
            ->beforeNormalization()
                ->ifTrue($hasLegacyEnv)->then($replaceLegacyEnv)
            ->end()
            ->children()
                ->scalarNode('SHELL')->defaultValue('/bin/bash')->end()
                ->scalarNode('TIMEOUT')->defaultValue(300)->end()
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
                                ->beforeNormalization()
                                    ->ifTrue($hasLegacyEnv)
                                    ->then($replaceEnvWithTargetEnv)
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