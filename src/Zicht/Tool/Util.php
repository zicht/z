<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool;


/**
 * Utility container
 */
final class Util
{
    /**
     * A wrapper for var_export, having list-style arrays (0-indexed incremental keys) compile without the keys in
     * the code.
     *
     * @param mixed $var
     * @return string
     */
    public static function toPhp($var)
    {
        switch (gettype($var)) {
            case 'array':
                $skipKeys = (range(0, count($var) - 1) === array_keys($var));
                $ret = 'array(';
                $i = 0;
                foreach ($var as $key => $value) {
                    if ($i++ > 0) {
                        $ret .= ', ';
                    }
                    if (!$skipKeys) {
                        $ret .= self::toPhp($key) . ' => ';
                    }
                    $ret .= self::toPhp($value);
                }
                $ret .= ')';
                break;
            default:
                $ret = var_export($var, true);
        }
        return $ret;
    }


    /**
     * Returns the type of the variable, and it's class if it's an object.
     *
     * @param mixed $what
     * @return string
     */
    public static function typeOf($what)
    {
        return is_object($what) ? get_class($what) : gettype($what);
    }
}