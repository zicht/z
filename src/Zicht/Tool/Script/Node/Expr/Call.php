<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */
namespace Zicht\Tool\Script\Node\Expr;

use \Zicht\Tool\Script\Buffer;
use \Zicht\Tool\Script\Node\Branch;
use \Zicht\Tool\Script\Node\Node;

/**
 * Represents a function call node
 */
class Call extends Branch
{
    /**
     * Constructor
     *
     * @param Node $function
     */
    public function __construct($function)
    {
        parent::__construct();
        $this->nodes[]= $function;
    }

    /**
     * @{inheritDoc}
     */
    public function compile(Buffer $buffer)
    {
        $buffer->raw('$z->call(');
        foreach ($this->nodes as $i => $n) {
            if ($i == 0) {
                $n->compile($buffer);
            } else {
                $buffer->raw(', ');
                $buffer->raw('$z->value(');
                $n->compile($buffer);
                $buffer->raw(')');
            }
        }
        $buffer->raw(')');
    }
}