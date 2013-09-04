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

class Unary extends Branch
{

    function __construct($operator, $subject)
    {
        parent::__construct();
        $this->operator = $operator;
        $this->nodes[0] = $subject;
    }


    public function compile(Buffer $compiler)
    {
        $compiler->write($this->operator);
        $this->nodes[0]->compile($compiler);
    }
}