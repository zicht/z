<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Plugin\Env;

use \Zicht\Tool\Plugin as BasePlugin;
use Zicht\Tool\Container\Container;
use \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class Plugin extends BasePlugin
{
    public function setContainer(Container $container)
    {
        $container['env.versionat'] = $container->protect(function($env, $verbose) use($container) {
            $tmp = tempnam(sys_get_temp_dir(), 'z');
            $container->cmd(sprintf(
                'scp %s:%s/%s %s',
                $container['env.ssh'],
                $container['env.root'],
                $container['vcs.export.revfile'],
                $tmp
            ));
            $vcsInfo = file_get_contents($tmp);
            unlink($tmp);
            if ($verbose) {
                return $vcsInfo;
            } else {
                return $container['vcs.versionid']($vcsInfo);
            }
        });
    }
}