<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Output;

use \Symfony\Component\Console\Formatter\OutputFormatter;
use \Symfony\Component\Console\Formatter\OutputFormatterStyleInterface;

class LinePrefixFormatter implements \Symfony\Component\Console\Formatter\OutputFormatterInterface
{
    protected $prefix = '';

    public function __construct(\Symfony\Component\Console\Formatter\OutputFormatterInterface $decoratedOutputFormatter) {
        $this->formatter = $decoratedOutputFormatter;
    }

    public function format($message)
    {
        return $this->formatter->format(preg_replace('/.*\n/', $this->prefix . '$0', $message));
    }

    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    public function getPrefix()
    {
        return $this->prefix;
    }

    public function setDecorated($decorated)
    {
        return $this->formatter->setDecorated($decorated);
    }

    public function isDecorated()
    {
        return $this->formatter->isDecorated();
    }

    public function setStyle($name, OutputFormatterStyleInterface $style)
    {
        return $this->formatter->setStyle($name, $style);
    }

    public function hasStyle($name)
    {
        return $this->formatter->hasStyle($name);
    }

    public function getStyle($name)
    {
        return $this->formatter->getStyle($name);
    }
}