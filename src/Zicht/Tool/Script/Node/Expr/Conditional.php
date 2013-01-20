<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Script\Node\Expr;

use Zicht\Tool\Script\Buffer;
use Zicht\Tool\Script\Node\Branch;

class Conditional extends Branch
{
    function __construct($node)
    {
        parent::__construct();
        $this->nodes[0] = $node;
    }


    public function compile(Buffer $compiler)
    {
        $this->nodes[0]->compile($compiler);
    }
}
