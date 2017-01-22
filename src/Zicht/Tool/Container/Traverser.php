<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */
namespace Zicht\Tool\Container;

/**
 * A tree traverser which can be used to alter a nested array tree structure
 */
class Traverser
{
    /**
     * If this is used as the visitor type, the visitor is called right BEFORE entering the child node list
     * of the node
     */
    const BEFORE = 1;

    /**
     * If this is used as the visitor type, the visitor is called right AFTER the children have been visited.
     */
    const AFTER = 2;

    /**
     * The tree to traverse
     *
     * @var array
     */
    protected $tree;

    /**
     * The visitors to apply to the tree
     * @var array
     */
    protected $visitors;


    /**
     * Construct the traverser with the specified tree as input.
     *
     * @param array $tree
     */
    public function __construct($tree)
    {
        $this->tree = $tree;
        $this->visitors = array();
    }


    /**
     * Add a visitor to the traverser. The node being visited is passed to the second callback to determine whether
     * the first callback should be called with the node. The arguments passed to both callbacks are the current node
     * and the path to the node. The result of the first callback is used to replace the node in the tree.
     *
     * Example:
     * <code>
     * $traverser->addVisitor(
     *     function($path, $node) {
     *         $node['i was visited'] = true;
     *         return $node;
     *     },
     *     function($path, $node) {
     *         return count($path) == 3 && $path[0] == 'foo' && $path[2] == 'bar';
     *     }
     * );
     *
     * $traverser->traverse(
     *     array(
     *         'foo' => array(
     *             array(
     *                 'bar' => array('i should be visited' => true),
     *                 'baz' => array('i should not be visited' => true)
     *             )
     *         )
     *     )
     * );
     * </code>
     *
     * @param callable $callable
     * @param callable $condition
     * @param int $when
     * @return self
     */
    public function addVisitor($callable, $condition, $when = self::BEFORE)
    {
        $this->visitors[] = array($when, $condition, $callable);

        return $this;
    }


    /**
     * Traverse the entire tree
     *
     * @return mixed
     */
    public function traverse()
    {
        return $this->doTraverse($this->tree);
    }


    /**
     * Recursive traversal implementation
     *
     * @param mixed $node
     * @param array $path
     * @return mixed
     */
    private function doTraverse($node, $path = array())
    {
        foreach ($node as $name => $value) {
            $path[] = $name;
            $value = $this->doVisit($path, $value, self::BEFORE);

            if (is_array($value)) {
                $value = $this->doTraverse($value, $path);
            }

            $value = $this->doVisit($path, $value, self::AFTER);
            $node[$name] = $value;
            array_pop($path);
        }

        return $node;
    }


    /**
     * Visits the node with all visitors at the specified time.
     *
     * @param array $path
     * @param mixed $value
     * @param int $when
     * @return mixed
     *
     * @throws \RuntimeException
     */
    private function doVisit($path, $value, $when)
    {
        foreach ($this->visitors as $visitor) {
            if ($visitor[0] === $when && call_user_func($visitor[1], $path, $value)) {
                try {
                    $value = call_user_func($visitor[2], $path, $value);
                } catch (\Exception $e) {
                    if ($path) {
                        $path = join('.', $path);
                    }
                    $path = json_encode($path);
                    $value = json_encode($value);
                    throw new \RuntimeException("While visiting value '{$value}' at path {$path}", 0, $e);
                }
            }
        }
        return $value;
    }
}