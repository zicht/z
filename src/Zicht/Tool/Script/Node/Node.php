<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */
namespace Zicht\Tool\Script\Node;
use Zicht\Tool\Script\Buffer;

/**
 * Base class for nodes that can have children.
 */
class Node implements NodeInterface
{
    /**
     * The child nodes.
     *
     * @var NodeInterface[]
     */
    public $nodes = [];
    public $attributes = [];

    /**
     * Constructor.
     *
     * @param NodeInterface[] $nodes
     */
    public function __construct(array $nodes = array())
    {
        $this->nodes = $nodes;
    }


    /**
     * Append a node.
     *
     * @param NodeInterface $node
     * @return void
     */
    public function append(NodeInterface $node)
    {
        $this->nodes[]= $node;
    }



    public function compile(Buffer $buffer)
    {
        foreach ($this->nodes as $node) {
            $node->compile($buffer);
        }
    }
}
