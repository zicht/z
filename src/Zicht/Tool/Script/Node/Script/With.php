<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Script\Node\Script;

use Zicht\Tool\Script\Buffer;
use Zicht\Tool\Script\Node\Branch;

/**
 * Class With
 *
 * @package Zicht\Tool\Script\Node\Script
 */
class With extends Branch implements Annotation
{
    /**
     * Construct the decorator with the specified expression as the first and only child node.
     *
     * @param \Zicht\Tool\Script\Node\Node $expr
     * @param string $name
     */
    public function __construct($expr, $name)
    {
        parent::__construct(array($expr));
        $this->name = $name;
    }

    /**
     * Allows the annotation to modify the buffer before the script is compiled.
     *
     * @param Buffer $buffer
     * @return void
     */
    public function beforeScript(Buffer $buffer)
    {
        $buffer->write(sprintf('$z->push(\'%s\', ', $this->name));
        $this->nodes[0]->compile($buffer);
        $buffer->write(');');
    }

    /**
     * Allows the annotation to modify the buffer after the script is compiled.
     *
     * @param Buffer $buffer
     * @return void
     */
    public function afterScript(Buffer $buffer)
    {
        $buffer->write(sprintf('$z->pop(\'%s\');', $this->name));
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
