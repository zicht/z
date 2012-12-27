<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Container;


class Compiler
{
    public static function isList($node)
    {
        return is_array($node) && array_keys($node) === range(0, count($node) -1);
    }


    function __construct($containerName = 'z')
    {
        $this->containerName = $containerName;
    }


    function getContainerName() {
        return $this->containerName;
    }


    function compile($input, $prefix = '')
    {
        $ret = '';
        foreach ($input as $name => $node) {
            if (self::isList($node) || is_scalar($node) || is_null($node)) {
                $ret .= $this->compileValue($prefix . $name, var_export($node, true)) . "\n";
            } elseif (is_array($node)) {
                $ret .= $this->compile($node, $prefix . $name . '.');
            } elseif ($node instanceof Compilable) {
                $ret .= $this->compileValue($prefix . $name, $node->compile($this)) . "\n";
            } else {
                throw new InvalidArgumentException("Can not compile node at path {$prefix}{$name}.");
            }
        }
        return $ret;
    }


    function compileValue($name, $def) {
        $namePart = sprintf('$' . $this->containerName . '[%s]', var_export($name, true));
        return sprintf('%s = %s;', $namePart, $def);
    }
}
