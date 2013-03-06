<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Container;

use Zicht\Tool\Script\Buffer;
use Zicht\Tool\Script\Node\Node;

class ContainerNode extends \Zicht\Tool\Script\Node\Branch
{
    public function compile(Buffer $compiler)
    {
        $compiler->writeln('$z = new \Zicht\Tool\Container\Container();');

        foreach ($this->nodes as $node) {
            $node->compile($compiler);
        }
    }


    public function getTasks() {
        $ret = array();
        foreach ($this->nodes as $node) {
            if ($node instanceof Task) {
                $ret[]= $node;
            }
        }
        return $ret;
    }
}