<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Tool\Plugin\Sass;

use Zicht\Tool\Plugin as BasePlugin;
use Zicht\Tool\Container\Container;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class Plugin extends BasePlugin
{
    public function appendConfiguration(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('sass')
                    ->children()
                        ->scalarNode('build_style')->defaultValue('compressed')->end()
                        ->scalarNode('default_style')->defaultValue('nested')->end()
                        ->scalarNode('sass_dir')->defaultValue('sass')->end()
                        ->scalarNode('css_dir')->defaultValue('style')->end()
                        ->arrayNode('dirs')
                            ->isRequired()
                            ->requiresAtLeastOneElement()
                            ->prototype('scalar')->end()
                        ->end()
                        ->scalarNode('opts')->end()
                    ->end()
                ->end()
            ->end();
    }

    public function setContainer(Container $container)
    {
        $container->method('sass.dir_spec', function($container, $root, $dirs) {
            if (!count($dirs)) {
                throw new \InvalidArgumentException("Passed dirs to sass.dir_spec() are invalid, at least 1 element is required");
            }
            $ret = array();
            foreach ($dirs as $dir) {
                if (strpos(':', $dir) !== false) {
                    list($src, $tgt) = explode(':', $dir);
                } else {
                    $src = rtrim($dir, '/') . '/' . $container->resolve('sass.sass_dir');
                    $tgt = rtrim($dir, '/') . '/' . $container->resolve('sass.css_dir');
                }

                $ret[] = sprintf('%s:%s', rtrim($root, '/') . '/' . $src, rtrim($root, '/') . '/' . $tgt);
            }
            return join(' ', $ret);
        });
    }
}