<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Script\Node\Expr\Op;

use Zicht\Tool\Script\Node\Branch;
use Zicht\Tool\Script\Node\Node;
use Zicht\Tool\Script\Buffer;

/**
 * Represents a binary expression node.
 */
class Binary extends Branch
{
    /**
     * Constructor.
     *
     * @param string $operator
     * @param Node $left
     * @param Node $right
     */
    public function __construct($operator, $left, $right)
    {
        parent::__construct();
        $this->operator = $operator;
        $this->nodes[0] = $left;
        $this->nodes[1] = $right;
    }


    /**
     * @{inheritDoc}
     */
    public function compile(Buffer $buffer)
    {
        if ($this->operator === '=~') {
            $buffer->raw('(bool)preg_match((string)');
            $this->nodes[1]->compile($buffer);
            $buffer->raw(', (string)');
            $this->nodes[0]->compile($buffer);
            $buffer->raw(')');
        } elseif ($this->operator === 'in') {
            $buffer->raw('in_array(');
            $this->nodes[0]->compile($buffer);
            $buffer->raw(', (array)');
            $this->nodes[1]->compile($buffer);
            $buffer->raw(')');
        } else {
            $this->nodes[0]->compile($buffer);
            $buffer->raw($this->operator);
            $this->nodes[1]->compile($buffer);
        }
    }
}
