<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Script;

class AbstractParser
{
    function __construct($parent = null)
    {
        $this->parent = $parent;
    }

    function err($stream)
    {
        throw new \UnexpectedValueException("Unexpected input near {$stream->current()->value}");
    }
}