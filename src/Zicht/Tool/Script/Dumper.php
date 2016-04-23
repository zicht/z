<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */
namespace Zicht\Tool\Script;

use Zicht\Tool\Util;

/**
 * Dumps a compiled node AST as an array
 */
class Dumper
{
    /**
     * Returns the AST for a specified node as an array representation
     *
     * @param Node\Node $b
     * @param string $path
     * @return array
     */
    public function getAst(Node\Node $b, $path = '')
    {
        $ret = array(
            'type' => str_replace('Zicht\Tool\Script\Node\\', '', get_class($b))
        );
        if ($b instanceof Node\Branch) {
            if (count($b->nodes)) {
                $ret['nodes'] = array();

                foreach ($b->nodes as $n) {
                    if (null === $n) {
                        $ret['nodes'][]= $n;
                    } else {
                        if (! $n instanceof Node\Node) {
                            throw new \InvalidArgumentException("Invalid child node in " . Util::toPhp($path));
                        }
                        $ret['nodes'][]= $this->getAst($n);
                    }
                }
            }
        }

        return $ret;
    }
}
