<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Container;

/**
 * Represents a compilable node in the container configuration
 */
interface Compilable
{
    /**
     * Compile the node.
     *
     * @param Compiler $compiler
     * @return void
     */
    public function compile(Compiler $compiler);
}