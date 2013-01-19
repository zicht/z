<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Plugin\Svn;

use \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

use \Zicht\Tool\Container\Container;
use \Zicht\Tool\Plugin as BasePlugin;

/**
 * SVN plugin configuration
 */
class Plugin extends BasePlugin
{
    /**
     * Appends SVN configuration options
     *
     * @param \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $rootNode
     * @return mixed|void
     */
    public function appendConfiguration(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('vcs')
                    ->children()
                        ->scalarNode('url')->isRequired()->end()
                        ->arrayNode('export')
                            ->children()
                                ->scalarNode('revfile')->isRequired()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    public function setContainer(Container $container)
    {
        $container['versionof'] = $container->protect(function($dir) use($container) {
            if (is_file($revFile = ($dir . '/' . $container['vcs.export.revfile']))) {
                $info = file_get_contents($revFile);
            } else {
                $info = @shell_exec('svn info ' . $dir);
            }

            if (trim($info)) {
                preg_match('/^URL: (.*)/m', $info, $m);
                $url = $m[1];

                preg_match('/^Revision: (.*)/m', $info, $m);
                $rev = $m[1];

                return ltrim(str_replace($container['vcs.url'], '', $url), '/') . '@' . $rev;
            }
            return null;
        });
        $container['vcs.current'] = function($container) {
            return $container['versionof']($container['cwd']);
        };
    }
}