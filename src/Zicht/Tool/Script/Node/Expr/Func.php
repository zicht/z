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


class Func extends Branch
{
    public function __construct($name)
    {
        parent::__construct();
        $this->name = $name;
    }

    public function compile(Buffer $compiler)
    {
        $compiler->write('$z->call(' . var_export($this->name, true));
        foreach ($this->nodes as $i => $n) {
            $compiler->write(', ');
            $n->compile($compiler);
        }
        $compiler->write(')');
    }
}