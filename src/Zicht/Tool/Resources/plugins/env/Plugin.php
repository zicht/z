<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Plugin\Env;

use \Zicht\Tool\Plugin as BasePlugin;
use \Zicht\Tool\Container\Container;

/**
 * Provides some utilities related to environments.
 */
class Plugin extends BasePlugin
{
    /**
     * @{inheritDoc}
     */
    public function setContainer(Container $container)
    {
        $container->method(
            array('env', 'versionat'),
            function($container, $env, $verbose = false) {
                $tmp = tempnam(sys_get_temp_dir(), 'z');
                $cmd = sprintf(
                    'scp %s:%s/%s %s',
                    $container->resolve('env.' . $env . '.ssh'),
                    $container->resolve('env.' . $env . '.root'),
                    $container->resolve(array('vcs', 'export', 'revfile')),
                    $tmp
                );

                passthru($cmd);
                $vcsInfo = file_get_contents($tmp);
                unlink($tmp);
                if ($verbose) {
                    return $vcsInfo;
                } else {
                    return $container->call('vcs.versionid', $vcsInfo);
                }
            }
        );
        $container->fn(
            array('ssh', 'connectable'),
            function($ssh) {
                return shell_exec(sprintf('ssh -oBatchMode=yes %s "echo 1" 2>/dev/null;', $ssh));
            }
        );
    }
}