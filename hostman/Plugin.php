<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */
namespace Zicht\Tool\Plugin\Hostman;

use Zicht\Tool\Plugin as BasePlugin;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Zicht\Tool\Container\Container;

class Plugin extends BasePlugin
{
    public function appendConfiguration(ArrayNodeDefinition $rootNode)
    {
        parent::appendConfiguration($rootNode);

        $rootNode
            ->children()
                ->arrayNode('hostman')
                    ->children()
                        ->scalarNode('webroot')->end()
                        ->scalarNode('suffix')->end()
                        ->scalarNode('app_env')->end()
                        ->scalarNode('httpd')->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    public function setContainer(Container $container)
    {
//        var_dump($container->get('hostman.httpd'));
        $container->set('httpd', array(
            'initscript' => function($c) {
                switch ($c->get(array('hostman', 'httpd'))) {
                    case 'apache2':
                        return '/etc/init.d/apache2';
                        break;
                    case 'nginx':
                        return '/etc/init.d/nginx';
                        break;
                }
            }
        ));
    }
}