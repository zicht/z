<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */
namespace Zicht\Tool\Script\Node;

abstract class Branch implements Node
{
    /**
     * @var Node[]
     */
    public $nodes ;

    function __construct()
    {
        $this->nodes = array();
    }


    function append($node)
    {
        $this->nodes[]= $node;
    }
}
