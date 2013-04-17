<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Plugin\Ci;

use Zicht\Tool\Plugin as BasePlugin;
use Zicht\Tool\Container\Container;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;


class Plugin extends BasePlugin
{
    public function appendConfiguration(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('ci')
                    ->append($this->getBaseConfigNode('lint'))
                    ->append($this->getBaseConfigNode('phpdox', function($node) {
                        $node->children()->scalarNode('file')->end()->end();
                    }))
                    ->append($this->getBaseConfigNode('pdepend'))
                    ->append($this->getBaseConfigNode('phpmd'))
                    ->append($this->getBaseConfigNode('phpcs', function ($node) {
                        $node->children()->scalarNode('standard')->end()->end();
                    }))
                    ->append($this->getBaseConfigNode('phpcd'))
                    ->append($this->getBaseConfigNode('phploc'))
                    ->append($this->getBaseConfigNode('phpcb'))
                    ->append($this->getBaseConfigNode('phpunit', function($node){
                        $isString = function ($value) {
                            return is_string($value);
                        };
                        $node
                            ->children()
                                ->arrayNode('opts')
                                    ->beforeNormalization()
                                        ->ifTrue($isString)
                                        ->then(function($value) { return array($value); })
                                    ->end()
                                    ->prototype('scalar')->end()
                                ->end()
                            ->end()
                        ;
                    }))
                ->end()
            ->end()
        ;
    }


    public function setContainer(Container $container)
    {
        $container->fn('ci.resource', function($path) use($container) {
            return __DIR__ . '/' . $path;
        });
    }


    function getBaseConfigNode($name, $callable = null)
    {
        $treeBuilder = new \Symfony\Component\Config\Definition\Builder\TreeBuilder();
        $node = $treeBuilder->root($name);

        $boolAsArray = function($value) {
            return array(
                'enabled' => $value
            );
        };
        $isBool = function($value) {
            return is_bool($value);
        };
        $node
            ->beforeNormalization()
                ->ifTrue($isBool)->then($boolAsArray)
            ->end()
            ->children()
                ->booleanNode('enabled')->defaultValue(true)->end()
        ;
        if (is_callable($callable)) {
            call_user_func($callable, $node);
        }

        return $node;
    }
}
