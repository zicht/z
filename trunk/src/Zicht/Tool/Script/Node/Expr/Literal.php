<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Script\Node\Expr;

use Zicht\Tool\Script\Node\Node;
use Zicht\Tool\Script\Buffer;

class Literal implements Node
{
    public function __construct($value)
    {
        $this->value = $value;
    }

    public function compile(Buffer $compiler)
    {
        $compiler->write(var_export($this->value, true));
    }
}