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
        $container->method('vcs.versionid', function($container, $info) {
            if (
                trim($info)
                && preg_match('/^URL: (.*)/m', $info, $urlMatch)
                && preg_match('/^Revision: (.*)/m', $info, $revMatch)
            ) {
                $url = $urlMatch[1];
                $rev = $revMatch[1];
                $projectUrl = $container->resolve('vcs.url');

                if (substr($url, 0, strlen($projectUrl)) != $projectUrl) {
                    throw new \UnexpectedValueException("The project url {$projectUrl} does not match the VCS url {$url}");
                }

                return ltrim(str_replace($container->resolve('vcs.url'), '', $url), '/') . '@' . $rev;
            }
            return null;
        });
        $container->method('versionof', function($container, $dir) {
            if (is_file($revFile = ($dir . '/' . $container->resolve('vcs.export.revfile')))) {
                $info = file_get_contents($revFile);
            } elseif (is_dir($dir)) {
                $info = @shell_exec('svn info ' . $dir . ' 2>&1');
            } else {
                return null;
            }
            return $container->call('vcs.versionid', $info);
        });
        $container->decl('vcs.current', function($container) {
            return $container->call('versionof', $container->resolve('cwd'));
        });
    }
}