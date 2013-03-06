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


class Data implements Node
{
    function __construct($data)
    {
        $this->data = $data;
    }



    function compile(Buffer $compiler)
    {
        $compiler->asPhp($this->data);
    }
}