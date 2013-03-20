<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Container;

/**
 * The compiler compiles the service container definition based on the container configuration
 */
class Flattener
{
    /**
     * Checks if the passed variable is a list, which is a 0-indexed incremental array.
     *
     * @param mixed $node
     * @return bool
     */
    public static function isList($node)
    {
        if ($node === array()) {
            return true;
        }
        return is_array($node) && array_keys($node) === range(0, count($node) -1);
    }


    /**
     * Compile the input array.
     *
     * @param array $input
     * @param string $prefix
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function flatten($input, $prefix = '')
    {
        $ret = array();
        foreach ($input as $name => $node) {
            if (self::isList($node) || is_scalar($node) || is_null($node)) {
                $ret[$prefix . $name] = $node;
            } elseif (is_array($node)) {
                $ret = array_merge($ret, $this->flatten($node, $prefix . $name . '.'));
            } else {
                throw new \InvalidArgumentException("Can not compile node at path {$prefix}{$name}.");
            }
        }
        return $ret;
    }


    /**
     * Compiles a value definition for the container.
     *
     * @param string $name
     * @param string $valueDef
     * @return string
     */
    public function compileValue($name, $valueDef)
    {
        $namePart = sprintf('$' . $this->containerName . '[%s]', var_export($name, true));
        return sprintf('%s = %s;', $namePart, $valueDef);
    }
}
