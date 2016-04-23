<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Script\Node\Expr;

use Zicht\Tool\Script\Buffer;
use Zicht\Tool\Script\Node\Branch;
use Zicht\Tool\Script\Node\Node;

/**
 * Represents an expression node in a script.
 */
class Expr extends Branch
{
    /**
     * Constructor.
     *
     * @param Node $node
     */
    public function __construct($node)
    {
        parent::__construct();
        $this->nodes[0] = $node;
    }


    /**
     * @{inheritDoc}
     */
    public function compile(Buffer $buffer)
    {
        $buffer->raw('$z->value(');
        $this->nodes[0]->compile($buffer);
        $buffer->raw(')');
    }
}
