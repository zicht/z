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
use \Zicht\Tool\Script\Node\Branch;

/**
 * A parenthesized expression, i.e. enclosed in parentheses.
 */
class Parens extends Branch
{
    /**
     * Constructor.
     *
     * @param Node $node
     */
    public function __construct(Node $node)
    {
        parent::__construct();
        $this->nodes[0] = $node;
    }


    /**
     * @{inheritDoc}
     */
    public function compile(Buffer $buffer)
    {
        $buffer->raw('(');
        $this->nodes[0]->compile($buffer);
        $buffer->raw(')');
    }
}