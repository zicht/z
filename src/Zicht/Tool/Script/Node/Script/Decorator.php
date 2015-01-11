<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Script\Node\Script;

use \Zicht\Tool\Script\Buffer;
use \Zicht\Tool\Script\Node\Branch;
use \Zicht\Tool\Script\Node\Node;

/**
 * Represents a conditional for a script node.
 */
class Decorator extends Branch implements Annotation
{
    /**
     * Construct the decorator with the specified expression as the first and only child node.
     *
     * @param \Zicht\Tool\Script\Node\Node $expr
     */
    public function __construct($expr)
    {
        parent::__construct(array($expr));
    }

    /**
     * @{inheritDoc}
     */
    public function beforeScript(Buffer $buffer)
    {
        $buffer->writeln('$z->push("SHELL", ')->indent(1);
        $this->nodes[0]->compile($buffer);
        $buffer->indent(-1)->writeln(');');
    }

    /**
     * @{inheritDoc}
     */
    public function afterScript(Buffer $buffer)
    {
        $buffer->writeln('$z->pop("SHELL");');
    }

    /**
     * Compiles the node into the buffer.
     *
     * @param \Zicht\Tool\Script\Buffer $buffer
     * @return void
     */
    public function compile(Buffer $buffer)
    {
    }
}
