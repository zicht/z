<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Plugin\Say;

use Zicht\Tool\Plugin as BasePlugin;
use Zicht\Tool\Container\Container;

class Plugin extends BasePlugin
{
    public function setContainer(Container $container)
    {
        $container->decl(
            'hello',
            function() {
                return 'Hello world!';
            }
        );
    }
}