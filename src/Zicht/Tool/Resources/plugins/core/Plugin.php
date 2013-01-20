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

    public function setContainer(Container $container)
    {
        $container['now'] = date('Ymd-H.i.s');
        $container['date'] = date('Ymd');
        $container['cwd'] = getcwd();
        $container['if'] = $container->protect(
            function($condition, $then, $else = null) {
                if ($condition) {
                    return $then;
                } else {
                    return $else;
                }
            }
        );
        $container['ask'] = $container->protect(
            function($q, $default = null) use ($container) {
                return $container['console_dialog_helper']->ask(
                    $container['console_output'],
                    $q . ($default ? sprintf(' [<info>%s</info>]', $default) : '') . ': ',
                    $default
                );
            }
        );
        $container['sprintf'] = $container->protect(
            function($str) use ($container) {
                $args = func_get_args();
                $tpl = array_shift($args);
                return vsprintf($tpl, $args);
            }
        );
        $container['printf'] = $container->protect(
            function($str) use ($container) {
                $args = func_get_args();
                $tpl = array_shift($args);
                $container['stdout'](vsprintf($tpl, $args));
            }
        );
        $container['confirm']= $container->protect(
            function($q, $default = false) use ($container) {
                return $container['console_dialog_helper']->askConfirmation(
                    $container['console_output'],
                    $q .
                        ($default === false ? ' [y/N] ' : ' [Y/n]'),
                    $default
                );
            }
        );
        $container['unless'] = $container->protect(function($condition, $msg) {
            if (!$condition) {
                throw new \Zicht\Tool\Script\FlowControl\SkipTask($msg);
            }
        });
        $container['mtime'] = $container->protect(function($glob) {
            $ret = array();
            foreach (glob($glob) as $file) {
                $ret[]= filemtime($file);
            }
            return max($ret);
        });
        $container['is_dir'] = 'is_dir';
        $container['is_file'] = 'is_file';
        $container['url.host'] = $container->protect(function($url) {
            return parse_url($url, PHP_URL_HOST);
        });

        $self = $this;
        $outputDecorator = function($original) use($self, $container) {
            return function($output) use($original, $self, $container) {
                if ($container['verbose']) {
                    return $original(
                        preg_replace(
                            '/(.*)\n/', '[<info>' . join('</info>][<info>', $self->prefix) . '</info>] $1' . "\n",
                            $output
                        )
                    );
                } else {
                    return $original($output);
                }
            };
        };

        $container->decorate('stdout', $outputDecorator);

        $container->subscribe(
            function($task, $event) use($container, $self) {
                switch ($event) {
                    case 'start':
                        array_push($self->prefix, $task);
                        break;
                    case 'end':
                        array_pop($self->prefix);
                        break;
                }
            }
        );
    }
}