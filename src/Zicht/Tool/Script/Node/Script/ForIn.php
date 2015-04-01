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


/**
 * Class ForIn
 *
 * @package Zicht\Tool\Script\Node\Script
 */
class ForIn extends Branch implements Annotation
{
    /**
     * Construct the decorator with the specified expression as the first and only child node.
     *
     * @param \Zicht\Tool\Script\Node\Node $expr
     * @param string $key
     * @param string $value
     */
    public function __construct($expr, $key, $value)
    {
        parent::__construct(array($expr));
        $this->key = $key ?: '_key';
        $this->value = $value ?: '_value';
    }
    /**
     * Allows the annotation to modify the buffer before the script is compiled.
     *
     * @param Buffer $buffer
     * @return void
     */
    public function beforeScript(Buffer $buffer)
    {
        $buffer->write('foreach ((array)');
        $this->nodes[0]->compile($buffer);
        $buffer
            ->raw(' as $_key => $_value) {')->eol()->indent(1)
            ->writeln(sprintf('$z->push(\'%s\', $_key);', $this->key))
            ->writeln(sprintf('$z->push(\'%s\', $_value);', $this->value))
        ;
    }

    /**
     * Allows the annotation to modify the buffer after the script is compiled.
     *
     * @param Buffer $buffer
     * @return void
     */
    public function afterScript(Buffer $buffer)
    {
        $buffer->write(sprintf('$z->pop(\'%s\');', $this->key));
        $buffer->write(sprintf('$z->pop(\'%s\');', $this->value));
        $buffer->indent(-1);
        $buffer->writeln('}');
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