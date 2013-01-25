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
    }


    /**
     * Write some code to the buffer
     *
     * @param string $data
     * @return Buffer
     */
    public function write($data)
    {
        $this->result .= $data;
        return $this;
    }


    public function writeln($data)
    {
        $this->result .= $data . PHP_EOL;
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
}