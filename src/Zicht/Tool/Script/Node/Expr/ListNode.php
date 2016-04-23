<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Tool\Script\Node\Expr;

use Zicht\Tool\Script\Buffer;
use Zicht\Tool\Script\Node\Branch;


/**
 * Represents a list (0-indexed numeric key array).
 */
class ListNode extends Branch
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
            $child->compile($buffer);
        }
        $buffer->raw(')');
    }
}
