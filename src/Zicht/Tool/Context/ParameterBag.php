<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Context;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag as BaseParameterBag;

class ParameterBag extends BaseParameterBag
{
    function setPath($path, $value) {
        $path = explode('.', $path);
        $ptr =& $this->parameters;
        foreach (array_slice($path, 0, -1) as $element) {
            if (!isset($ptr[$element])) {
                $ptr[$element] = array();
            }
            $ptr =& $ptr[$element];
        }
        $ptr[end($path)] = $value;
    }


    function getPath($path) {
        $ptr = $this->parameters;
        foreach (explode('.', $path) as $element) {
            if (!isset($ptr[$element])) {
                $ptr = null;
                break;
            }
            $ptr =& $ptr[$element];
        }
        return $ptr;
    }
}