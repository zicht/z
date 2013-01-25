<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Plugin\Core;

use \Zicht\Tool\Plugin as BasePlugin;
use Zicht\Tool\Container\Container;
use \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class Plugin extends BasePlugin
{
    public $prefix = array();
    public $prefixer = null;

    public function setContainer(Container $container)
    {
        $container->set('now', date('Ymd-H.i.s'));
        $container->set('date', date('Ymd'));
        $container->set('cwd', getcwd());

        $container->method('ask', function($container, $q, $default = null) {
                return $container['console_dialog_helper']->ask(
                    $container->output,
                    $q . ($default ? sprintf(' [<info>%s</info>]', $default) : '') . ': ',
                    $default
                );
            }
        );
        $container->fn('sprintf');
        $container->fn('is_dir');
        $container->fn('is_file');

//        $container['confirm']= $container->protect(
//            function($q, $default = false) use ($container) {
//                return $container['console_dialog_helper']->askConfirmation(
//                    $container->output,
//                    $q .
//                        ($default === false ? ' [y/N] ' : ' [Y/n]'),
//                    $default
//                );
//            }
//        );
        $container->fn('mtime', function($glob) {
            $ret = array();
            foreach (glob($glob) as $file) {
                $ret[]= filemtime($file);
            }
            return max($ret);
        });
        $container->fn('url.host', function($url) {
            return parse_url($url, PHP_URL_HOST);
        });
    }
}