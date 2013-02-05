<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Plugin\Randomizer;

use Zicht\Tool\Plugin as BasePlugin;
use Zicht\Tool\Container\Container;

class Plugin extends BasePlugin
{
    public function setContainer(Container $container)
    {
        $container->decl('one_time_random', function() {
            return rand(0, 100);
        });
        $container->fn('all_time_random', function($max = 100, $min = 0) {
            return rand($min, $max);
        });
    }
}
