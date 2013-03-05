<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Script\Node\Expr;

use Zicht\Tool\Script\Node\Branch;
use Zicht\Tool\Script\Buffer;

class Subscript extends Branch
{
    function __construct($n)
    {
        parent::__construct();
        $this->append($n);
    }


    public function compile(Buffer $compiler)
    {
        $compiler->write('$z->lookup(');
        foreach ($this->nodes as $i => $node) {
            if ($i > 0) {
                $compiler->write(',');
                $compiler->write('array(');
            }
            $node->compile($compiler);
        }
        $compiler->write('))');
    }
}