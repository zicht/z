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
abstract class Branch implements Node
{
    /**
     * The child nodes.
     *
     * @var Node[]
     */
    public $nodes;

    /**
     * Constructor.
     *
     * @param Node[] $nodes
     */
    public function __construct(array $nodes = array())
    {
        $this->nodes = $nodes;
    }


    /**
     * Append a node.
     *
     * @param Node $node
     * @return void
     */
    public function append(Node $node)
    {
        $this->nodes[]= $node;
    }
}
