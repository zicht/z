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
    public function __construct($expr)
    {
        parent::__construct(array($expr));
    }

    public function beforeScript(Buffer $buffer)
    {
        $buffer
            ->writeln('if (!isset($GLOBALS[\'_shell_stack\'])) {')->indent(1)
            ->writeln('$GLOBALS[\'_shell_stack\'] = array();')->indent(-1)
            ->writeln('}')
            ->writeln('array_push($GLOBALS[\'_shell_stack\'], $z->get(\'SHELL\'));')
            ->writeln('$z->set("SHELL", ')
        ;
        $this->nodes[0]->compile($buffer);
        $buffer->writeln(');');
    }

    public function afterScript(Buffer $buffer)
    {
        $buffer->writeln('$z->set("SHELL", array_pop($GLOBALS[\'_shell_stack\']));');
    }

    /**
     * Compiles the node into the buffer.
     *
     * @param \Zicht\Tool\Script\Buffer $buffer
     * @return void
     */
    public function compile(Buffer $buffer)
    {
//        $this->
    }
}