<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Script;

use \Zicht\Tool\Util;

/**
 * Buffer to write compiled code to
 */
class Buffer
{
    /**
     * The result buffer
     *
     * @var string
     */
    protected $result;


    /**
     * Initialize an empty buffer
     */
    public function __construct()
    {
        $this->result = '';
        $this->indent = 0;
    }


    /**
     * Write some code to the buffer
     *
     * @param string $data
     * @return self
     */
    public function write($data)
    {
        return $this->indent()->raw($data);
    }


    /**
     * Write an EOL character
     *
     * @return self
     */
    public function eol()
    {
        $this->result .= PHP_EOL;
        return $this;
    }

    /**
     * Write some data without formatting.
     *
     * @param string $data
     * @return self
     */
    public function raw($data)
    {
        $this->result .= $data;
        return $this;
    }


    /**
     * Write a line with indentation and a newline at the end.
     *
     * @param string $data
     * @return self
     */
    public function writeln($data)
    {
        return $this->write($data)->eol();
    }


    /**
     * Adds indentation to the buffer if $increment is not specified. Otherwise increment the current indentation
     * $increment steps. You should pass a negative number to outdent.
     *
     * @param bool $increment
     * @return Buffer
     */
    public function indent($increment = null)
    {
        if (null !== $increment) {
            $this->indent += $increment;
            if ($this->indent < 0) {
                throw new \InvalidArgumentException("Indent can not reach below zero!");
            }
        } else {
            $this->raw(str_repeat('    ', $this->indent));
        }
        return $this;
    }


    /**
     * Return the buffer contents.
     *
     * @return string
     */
    public function getResult()
    {
        return $this->result;
    }


    /**
     * Shorthand method to add the specified variable as it's php representation.
     *
     * @param mixed $var
     * @return Buffer
     */
    public function asPhp($var)
    {
        return $this->raw(Util::toPhp($var));
    }
}
