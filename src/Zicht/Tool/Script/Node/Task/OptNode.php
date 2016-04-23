<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Script\Node\Task;

use Zicht\Tool\Util;
use Zicht\Tool\Script\Node\Expr\Literal;

/**
 * A node for the "args" section of a task
 */
class OptNode extends ArgNode
{
    /**
     * Constructor.
     *
     * @param string $name
     * @param \Zicht\Tool\Script\Node\Node $expr
     */
    public function __construct($name, $expr)
    {
        if (!$expr) {
            $expr = new Literal("");
        }
        parent::__construct($name, $expr, true);
    }
}
