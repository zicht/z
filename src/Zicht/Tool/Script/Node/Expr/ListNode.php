<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Tool\Script\Node\Expr;

use Zicht\Tool\Script\Buffer;
use Zicht\Tool\Script\Node\Node;


/**
 * Represents a list (0-indexed numeric key array).
 */
class ListNode extends Node
{
    /**
     * @{inheritDoc}
     */
    public function compile(Buffer $buffer)
    {
        $buffer->raw('array(');
        $i = 0;
        foreach ($this->nodes as $child) {
            if ($i++ > 0) {
                $buffer->raw(', ');
            }
            if (isset($child->attributes['name'])) {
                $buffer->asPhp($child->attributes['name']);
                $buffer->raw(' => ');
            }
            $child->compile($buffer);
        }
        $buffer->raw(')');
    }
}
