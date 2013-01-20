<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Script\Node\Expr\Op;

use Zicht\Tool\Script\Node\Branch;
use Zicht\Tool\Script\Buffer;

class Ternary extends Branch
{
    function __construct($operator, $condition, $then, $else)
    {
        parent::__construct();
        $this->operator = $operator;
        $this->nodes[0] = $condition;
        $this->nodes[1] = $then;
        $this->nodes[2] = $else;
    }


    public function compile(Buffer $compiler)
    {
        $this->nodes[0]->compile($compiler);
        $compiler->write('?');
        if ($this->nodes[1]) {
            $this->nodes[1]->compile($compiler);
        }
        $compiler->write(':');
        if ($this->nodes[2]) {
            $this->nodes[2]->compile($compiler);
        } else {
            $compiler->write('null');
        }
    }
}