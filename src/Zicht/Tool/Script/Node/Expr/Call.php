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


class Call extends Branch
{
    public function __construct($left)
    {
        parent::__construct();
        $this->nodes[]= $left;
    }


    public function compile(Buffer $compiler)
    {
        $compiler->write('$z->call(');
        foreach ($this->nodes as $i => $n) {
            if ($i == 0) {
                $n->compile($compiler);
            } else {
                $compiler->write(', ');
                $compiler->write('$z->value(');
                $n->compile($compiler);
                $compiler->write(')');
            }
        }
        $compiler->write(')');
    }
}