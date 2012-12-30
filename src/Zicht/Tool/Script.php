<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool;

use \Zicht\Tool\Container\Container;
use \UnexpectedValueException;

/**
 * Script compiler for script snippets in the tool
 */
class Script
{
    /**
     * Construct the script with the specified string as input
     *
     * @param string $str
     */
    public function __construct($str)
    {
        $this->str = $str;
    }


    /**
     * Evaluate the script against the specified container.
     *
     * @param Container $c
     * @return string
     */
    public function evaluate(Container $c)
    {
        $self = $this;
        return preg_replace_callback(
            '/(.?)\$\(([\w+.]+)\)/',
            function($m) use($c, $self) {
                if ($m[1] == '$') {
                    return substr($m[0], 1);
                }
                if (!isset($c[$m[2]])) {
                    throw new UnexpectedValueException("Unable to resolve '{$m[2]}' in script '{$self->str}'");
                }
                $value = $c->evaluate($c[$m[2]]);
                return $m[1] . (is_array($value) ? join(' ', $value) : (string)$value);
            },
            $this->str
        );
    }
}