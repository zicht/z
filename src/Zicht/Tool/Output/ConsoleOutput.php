<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Output;

use Symfony\Component\Console\Output\ConsoleOutput as BaseConsoleOutput;

class ConsoleOutput extends BaseConsoleOutput
{
    protected $prefix = '';

    public function write($messages, $newline = false, $type = 0)
    {
        parent::write(preg_replace('/(.*)\n/', $this->prefix . '$1' . "\n", $messages), $newline, $type);
    }


    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }
}