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
    function compile(Buffer $c)
    {
        foreach ($this->nodes as $i => $node) {
            if ($i > 0) {
                $c->write(' . ');
            }
            $node->compile($c);
        }
    }
}