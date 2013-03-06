<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Tool\Script\Node\Expr;

use Zicht\Tool\Script\Buffer;
use Zicht\Tool\Script\Node\Branch;


class ListNode extends Branch
{
    public function compile(Buffer $compiler)
    {
        $compiler->raw('array(');
        $i = 0;
        foreach ($this->nodes as $child) {
            if ($i++ > 0) {
                $compiler->raw(', ');
            }
            $child->compile($compiler);
        }
        $compiler->raw(')');
    }
}