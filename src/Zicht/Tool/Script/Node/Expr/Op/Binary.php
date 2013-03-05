<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Script\Node\Expr\Op;

use Zicht\Tool\Script\Node\Branch;
use Zicht\Tool\Script\Node\Node;
use Zicht\Tool\Script\Buffer;

class Binary extends Branch
{
    function __construct($operator, $left, $right)
    {
        parent::__construct();
        $this->operator = $operator;
        $this->nodes[0] = $left;
        $this->nodes[1] = $right;
    }


    public function compile(Buffer $compiler)
    {
        //@deprecated, to be removed in 1.2
        if ($this->operator === 'cat') {
            $this->operator = '.';
        }
        $this->nodes[0]->compile($compiler);
        $compiler->write($this->operator);
        $this->nodes[1]->compile($compiler);
    }
}