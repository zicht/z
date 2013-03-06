<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Script\Node;
use Zicht\Tool\Script\Buffer;

class Definition implements Node
{
    function __construct($path, $value)
    {
        $this->path = $path;
        $this->value = $value;
    }

    public function compile(Buffer $compiler)
    {
        $compiler->write('$z->set(')->asPhp($this->path)->raw(',')->asPhp($this->value)->raw(');')->eol();
    }
}