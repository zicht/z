<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool;

use \Zicht\Tool\Container\Container;

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
     * @param Container $z
     * @return string
     */
    public function evaluate(Container $z)
    {
        $compiler = new \Zicht\Tool\Script\Compiler();
        $code = $compiler->compile($this->str);

        $_result = null;
        eval('$_result = ' . $code . ';');
        return $_result;
    }
}