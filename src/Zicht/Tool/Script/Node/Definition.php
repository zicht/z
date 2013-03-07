<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Script\Node;

use \Zicht\Tool\Script\Buffer;

/**
 * Represents a value declaration in the container context.
 */
class Definition implements Node
{
    /**
     * Constructor.
     *
     * @param array $path
     * @param mixed $value
     */
    public function __construct(array $path, $value)
    {
        $this->path = $path;
        $this->value = $value;
    }

    /**
     * @{inheritDoc}
     */
    public function compile(Buffer $buffer)
    {
        $buffer->write('$z->set(')->asPhp($this->path)->raw(',')->asPhp($this->value)->raw(');')->eol();
    }
}