<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool;


class Script
{
    function __construct($str) {
        $this->str = $str;
    }


    function evaluate(\Zicht\Tool\Container\Container $c) {
        $self = $this;
        return preg_replace_callback(
            '/(.?)\$\(([\w+.]+)\)/',
            function($m) use($c, $self) {
                if ($m[1] == '$') {
                    return substr($m[0], 1);
                }
                if (!isset($c[$m[2]])) {
                    throw new \UnexpectedValueException("Unable to resolve '{$m[2]}' in script '{$this->str}'");
                }
                $value = $c[$m[2]];
                return $m[1] . (is_array($value) ? join(' ', $value) : (string)$value);
            },
            $this->str
        );
    }
}