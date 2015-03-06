<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Container;

use \Zicht\Tool\Script\Buffer;
use \Zicht\Tool\Script\Node\Branch;
use Zicht\Tool\Script\Node\Node;

/**
 * The root node of a container definition
 */
class ContainerNode extends Branch
{
    /**
     * @{inheritDoc}
     */
    public function compile(Buffer $buffer)
    {
        $date = new \DateTime();
        $buffer->writeln('/** Container compiled by ' . getenv('USER') . ' at: ' . $date->format('r') . ' */');
        $buffer->writeln('$z = new \Zicht\Tool\Container\Container();');

        foreach ($this->nodes as $node) {
            $node->compile($buffer);
        }
    }
}