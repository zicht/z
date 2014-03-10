<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Script\Node\Script;

use \Zicht\Tool\Script\Buffer;
use \Zicht\Tool\Script\Node\Branch;
use \Zicht\Tool\Script\Node\Node;

/**
 * Represents a conditional for a script node.
 */
class Decorator extends Branch implements Annotation
{
    public function __construct($name, array $nodes = array())
    {
        parent::__construct($nodes);

        $this->name = $name;
    }

    public function beforeScript(Buffer $buffer)
    {

    }

    public function afterScript(Buffer $buffer)
    {
        // TODO: Implement afterScript() method.
    }

    /**
     * Compiles the node into the buffer.
     *
     * @param \Zicht\Tool\Script\Buffer $buffer
     * @return void
     */
    public function compile(Buffer $buffer)
    {
//        $this->
    }
}