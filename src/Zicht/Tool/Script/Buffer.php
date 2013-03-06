<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Script;

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
     * @return Buffer
     */
    public function write($data)
    {
        return $this->indent()->raw($data);
    }


    public function eol()
    {
        $this->result .= PHP_EOL;
        return $this;
    }

    public function raw($data)
    {
        $this->result .= $data;
        return $this;
    }


    public function writeln($data)
    {
        return $this->write($data)->eol();
    }


    public function indent($increment = false)
    {
        if (false !== $increment) {
            $this->indent += $increment;
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



    public function asPhp($var)
    {
        return $this->raw(\Zicht\Tool\Util::toPhp($var));
    }
}