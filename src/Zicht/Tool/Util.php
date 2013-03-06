<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool;


final class Util
{
    static function toPhp($var)
    {
        switch (gettype($var)) {
            case 'array':
                if (range(0, count($var)-1) === array_keys($var)) {
                    $ret = 'array(';
                    $i = 0;
                    foreach ($var as $value) {
                        if ($i ++ > 0) {
                            $ret .= ', ';
                        }
                        $ret .= self::toPhp($value);
                    }
                    $ret .= ')';
                    break;
                }
                // intended fallthrough for associative arrays.
            default:
                $ret = var_export($var, true);
        }
        return $ret;
    }
}