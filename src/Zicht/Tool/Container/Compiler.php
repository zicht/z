<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Container;

/**
 * The compiler compiles the service container definition based on the container configuration
 */
class Compiler
{
    /**
     * Checks if the passed variable is a list, which is a 0-indexed incremental array.
     *
     * @param mixed $node
     * @return bool
     */
    public static function isList($node)
    {
        return is_array($node) && array_keys($node) === range(0, count($node) -1);
    }


    /**
     * Construct the compiler with the passed container name as a variable to use for the container definition.
     *
     * @param string $containerName
     */
    public function __construct($containerName = 'z')
    {
        $this->containerName = $containerName;
    }


    /**
     * Returns the variable name used in the compiled PHP code for the container to be compiled.
     *
     * @return string
     */
    public function getContainerName()
    {
        return $this->containerName;
    }


    /**
     * Compile the input array.
     *
     * @param array $input
     * @param string $prefix
     * @return string
     */
    public function compile($input, $prefix = '')
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
