<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Script\Node\Expr;

use Zicht\Tool\Script\Node\Node;
use Zicht\Tool\Script\Buffer;

/**
 * Base class for literal nodes.
 */
class Literal implements Node
{
    /**
     * Constructor.
     *
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @{inheritDoc}
     */
    public function compile(Buffer $buffer)
    {
        $buffer->asPhp($this->value);
    }
}
