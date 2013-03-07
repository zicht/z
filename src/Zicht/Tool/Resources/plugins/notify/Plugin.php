<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Plugin\Notify;

use \Zicht\Tool\Plugin as BasePlugin;
use \Zicht\Tool\Container\Container;
use \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * Plugin for notification of external services.
 */
class Plugin extends BasePlugin
{
    /**
     * @{inheritDoc}
     */
    public function setContainer(Container $container)
    {
        $container->subscribe(array($this, 'propagate'));
    }


    /**
     * @{inheritDoc}
     */
    public function appendConfiguration(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('notify')
                    ->prototype('array') // tasks
                        ->prototype('array') // events
                            ->children()
                                ->scalarNode('url')->isRequired()->end()
                                ->arrayNode('post')
                                    ->prototype('scalar')->isRequired()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        parent::appendConfiguration($rootNode);
    }


    /**
     * @{inheritDoc}
     */
    public function propagate($task, $event, $container)
    {
        try {
            if (isset($container->config['notify'][$task][$event])) {
                $notifyConfig = $container->config['notify'][$task][$event];
                $post = array();
                foreach ($notifyConfig['post'] as $key => $value) {
                    $post[$key] = $container->evaluate($value);
                }
                $container->cmd('curl -d\'' . json_encode($post) . '\' \'' . $notifyConfig['url'] . '\'');
            }
        } catch (\Exception $e) {
        }
    }
}