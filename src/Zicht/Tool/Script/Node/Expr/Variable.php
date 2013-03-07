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
use \Zicht\Tool\Util;

/**
 * A variable node refers a variable in the container context.
 */
class Variable implements Node
{
    /**
     * @var array
     */
    protected $name;


    /**
     * Constructor.
     *
     * @param array $name
     * @return void
     */
    public function __construct($name)
    {
        $this->name = $name;
    }


    /**
     * Compiles the variable reference into the buffer.
     *
     * @param \Zicht\Tool\Script\Buffer $buffer
     * @return mixed|void
     */
    public function compile(Buffer $buffer)
    {
        $buffer->raw('$z->resolve(' . Util::toPhp($this->name) . ')');
    }
}