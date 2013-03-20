<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Script\Node\Expr;

use Zicht\Tool\Script\Buffer;

class Parens extends \Zicht\Tool\Script\Node\Branch
{
    function __construct($node)
    {
        parent::__construct();
        $this->nodes[0] = $node;
    }


    public function compile(Buffer $compiler)
    {
        $compiler->write('(');
        $this->nodes[0]->compile($compiler);
        $compiler->write(')');
    }
}