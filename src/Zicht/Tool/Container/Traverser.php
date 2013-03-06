<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */
namespace Zicht\Tool\Container;

class Traverser
{
    const BEFORE = 1;
    const AFTER = 2;

    function __construct($config)
    {
        $this->config = $config;
    }


     function addVisitor($callable, $condition, $when = self::BEFORE)
    {
        $this->visitors[]= array($when, $condition, $callable);
    }


    function traverse()
    {
        return $this->_traverse($this->config);
    }


    private function _traverse($node, $path = array())
    {
        foreach ($node as $name => $value) {
            $path[]= $name;
            $value = $this->visit($path, $value, self::BEFORE);

            if (is_array($value)) {
                $value = $this->_traverse($value, $path);
            }

            $value = $this->visit($path, $value, self::AFTER);
            $node[$name] = $value;
            array_pop($path);
        }

        return $node;
    }


    private function visit($path, $value, $when)
    {
        foreach ($this->visitors as $visitor) {
            if ($visitor[0] === $when && call_user_func($visitor[1], $path, $value)) {
                $value = call_user_func($visitor[2], $path, $value);
            }
        }
        return $value;
    }
}