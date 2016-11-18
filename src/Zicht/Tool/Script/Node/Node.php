<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */
namespace Zicht\Tool\Script\Node;

/**
 * Base class for nodes that can have children.
 */
abstract class Node implements NodeInterface
{
    /**
     * The child nodes.
     *
     * @var NodeInterface[]
     */
    public $nodes;

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
}
