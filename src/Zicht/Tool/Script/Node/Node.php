<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Script\Node;

use \Zicht\Tool\Script\Buffer;

/**
 * Common interface for syntax tree nodes.
 */
interface Node
{
    /**
     * Compiles the node into the buffer.
     *
     * @param \Zicht\Tool\Script\Buffer $buffer
     * @return mixed
     */
    public function compile(Buffer $buffer);
}