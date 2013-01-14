<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Script;

class Buffer
{
    protected $result;

    function __construct()
    {
        $this->result = '';
    }


    function write($data)
    {
        $this->result .= $data;
    }


    function getResult()
    {
        return $this->result;
    }
}