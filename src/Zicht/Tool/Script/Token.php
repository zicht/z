<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Script;

final class Token
{
    const DATA = 'data';
    const EXPR_START = 'expr_start';
    const EXPR_END = 'expr_end';
    const IDENTIFIER = 'identifier';
    const WHITESPACE = 'whitespace';

    public $type;
    public $value;

    function __construct($type, $value = null)
    {
        $this->type = $type;
        $this->value = $value;
    }


    function match($type, $value = null)
    {
        if ($this->type === $type) {
            if (null === $value || $this->value == $value) {
                return true;
            }
        }
        return false;
    }
}