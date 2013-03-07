<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Script\Node\Expr;

use \Zicht\Tool\Script\Buffer;
use \Zicht\Tool\Script\Node\Node;


/**
 * Represents a raw data node inside a script
 */
class Data implements Node
{
    /**
     * Construct the node.
     *
     * @param string $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }


    /**
     * @{inheritDoc}
     */
    public function compile(Buffer $buffer)
    {
        $buffer->asPhp($this->data);
    }
}