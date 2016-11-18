<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Script\Node\Expr;

use Zicht\Tool\Script\Node\Node;
use Zicht\Tool\Script\Node\NodeInterface;
use Zicht\Tool\Script\Buffer;

/**
 * A subscript node is a node that refers another element inside the node, either though dot notation (a,b) or bracket
 * notation (a["b"]).
 */
class Subscript extends Node
{
    /**
     * Constructor.
     *
     * @param NodeInterface $n
     */
    public function __construct(NodeInterface $n)
    {
        parent::__construct();
        $this->append($n);
    }


    /**
     * @{inheritDoc}
     */
    public function compile(Buffer $buffer)
    {
        $buffer->raw('$z->lookup(');
        foreach ($this->nodes as $i => $node) {
            if ($i > 0) {
                $buffer->raw(', ');
                $buffer->raw('array(');
            }
            $node->compile($buffer);
        }
        $buffer->raw('))');
    }
}
