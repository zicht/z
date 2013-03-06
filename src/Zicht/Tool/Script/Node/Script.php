<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Script\Node;


use Zicht\Tool\Script\Buffer;

class Script extends Branch
{
    function compile(Buffer $compiler)
    {
        if (count($this->nodes)) {
            $depth = 0;
            if ($this->nodes[0] instanceof Expr\Conditional) {
                $nodes = $this->nodes;
                $compiler->write('if (');
                $this->nodes[0]->compile($compiler);
                $compiler->raw(') {')->eol()->indent(1);
                array_shift($nodes);
                $depth ++;
            } else {
                $nodes = $this->nodes;
            }

            $compiler->write('$z->cmd(');
            foreach ($nodes as $i => $node) {
                if ($i > 0) {
                    $compiler->raw(' . ');
                }
                $node->compile($compiler);
            }
            $compiler->raw(');')->eol();

            while ($depth--) {
                $compiler->indent(-1)->writeln('}');
            }
        }
    }
}