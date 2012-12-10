<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Versioning;

class Factory
{
    static function get($options)
    {
        $parts = parse_url($options['url']);

        switch($parts['scheme']) {
            case 'svn':
                return new Svn\Versioning($options['url']);
                break;
            case 'git':

                break;
        }
        var_dump($parts);
        return $parts;
    }
}