<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Script\Node\Expr\Op;

use Zicht\Tool\Script\Node\Node;
use Zicht\Tool\Script\Node\NodeInterface;
use Zicht\Tool\Script\Buffer;

/**
 * Represents a ternary expression
 */
class Ternary extends Node
{
    /**
     * Constructor.
     *
     * @param string $operator
     * @param NodeInterface $condition
     * @param NodeInterface $then
     * @param NodeInterface $else
     */
    public function __construct($operator, $condition, $then, $else)
    {
        parent::__construct();
        $this->operator = $operator;
        $this->nodes[0] = $condition;
        $this->nodes[1] = $then;
        $this->nodes[2] = $else;
    }


    /**
     * @{inheritDoc}
     */
    public function compile(Buffer $buffer)
    {
        $this->nodes[0]->compile($buffer);
        $buffer->raw('?');
        if ($this->nodes[1]) {
            $this->nodes[1]->compile($buffer);
        }
        $buffer->raw(':');
        if ($this->nodes[2]) {
            $this->nodes[2]->compile($buffer);
        } else {
            $buffer->raw('null');
        }
    }
}
