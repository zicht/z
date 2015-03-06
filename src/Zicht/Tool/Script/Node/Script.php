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
        /** @var Script\Annotation[] $annotations */
        $annotations = array();
        $nodes = $this->nodes;
        while (current($nodes) instanceof Script\Annotation) {
            $annotations[]= array_shift($nodes);
        }

        foreach ($annotations as $annotation) {
            $annotation->beforeScript($buffer);
        }

        if (count($this->nodes)) {
            $buffer->write('$z->cmd(');
            foreach ($nodes as $i => $node) {
                if ($i > 0) {
                    $buffer->raw(' . ');
                }
                $buffer->raw('$z->str(');
                $node->compile($buffer);
                $buffer->raw(')');
            }
            $buffer->raw(');')->eol();
        }

        foreach ($annotations as $annotation) {
            $annotation->afterScript($buffer);
        }
    }
}