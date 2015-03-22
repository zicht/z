<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Output;

use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyleInterface;

class PrefixFormatter implements OutputFormatterInterface
{
    public $prefix = '';

    public function __construct(OutputFormatterInterface $innerFormatter)
    {
        $this->innerFormatter = $innerFormatter;
    }


    /**
     * @{inheritDoc}
     */
    public function setDecorated($decorated)
    {
        return $this->innerFormatter->setDecorated($decorated);
    }

    /**
     * @{inheritDoc}
     */
    public function isDecorated()
    {
        return $this->innerFormatter->isDecorated();
    }

    /**
     * @{inheritDoc}
     */
    public function setStyle($name, OutputFormatterStyleInterface $style)
    {
        return $this->innerFormatter->setStyle($name, $style);
    }

    /**
     * @{inheritDoc}
     */
    public function hasStyle($name)
    {
        return $this->innerFormatter->hasStyle($name);
    }

    /**
     * @{inheritDoc}
     */
    public function getStyle($name)
    {
        return $this->innerFormatter->getStyle($name);
    }

    /**
     * @{inheritDoc}
     */
    public function format($message)
    {
        $ret = $this->innerFormatter->format($message);

        if ($this->prefix) {
            $ret = preg_replace('/^/m', $this->prefix . '$1', $ret);
        }
        return $ret;
    }
}