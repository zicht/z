<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Script\Node\Expr;

use Zicht\Tool\Script\Buffer;
use Zicht\Tool\Script\Node\Node;

class Variable implements Node
{
    function __construct($name)
    {
        $this->name = $name;
    }



    function compile(Buffer $compiler)
    {
        $compiler->raw('$z->resolve(' . \Zicht\Tool\Util::toPhp($this->name) . ')');
    }
}