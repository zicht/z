<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Script\Node;

use \Zicht\Tool\Script\Buffer;

/**
 * A Script node is used as the body for each of the nodes executed in a pre, do, or post section in a Task
 */
class Script extends Branch
{
    /**
     * Compiles the node.
     *
     * @param \Zicht\Tool\Script\Buffer $buffer
     * @return void
     */
    public function compile(Buffer $buffer)
    {
        if (count($this->nodes)) {
            $depth = 0;
            if ($this->nodes[0] instanceof Expr\Conditional) {
                $nodes = $this->nodes;
                $buffer->write('if (');
                $this->nodes[0]->compile($buffer);
                $buffer->raw(') {')->eol()->indent(1);
                array_shift($nodes);
                $depth ++;
            } else {
                $nodes = $this->nodes;
            }

            $buffer->write('$z->cmd(');
            foreach ($nodes as $i => $node) {
                if ($i > 0) {
                    $buffer->raw(' . ');
                }
                $node->compile($buffer);
            }
            $buffer->raw(');')->eol();

            while ($depth--) {
                $buffer->indent(-1)->writeln('}');
            }
        }
    }
}