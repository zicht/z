<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Output;

use \Symfony\Component\Console\Formatter\OutputFormatter;
use \Symfony\Component\Console\Output\ConsoleOutput as BaseOutput;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * Provides a prefixer for the regular output.
 */
class ConsoleOutput extends BaseOutput
{
    /**
     * The current (string) prefix to be prepended to each output line
     *
     * @var string
     */
    protected $prefix = '';


    /**
     * @{inheritDoc}
     */
    public function write($messages, $newline = false, $type = 0)
    {
        if (OutputInterface::VERBOSITY_QUIET === $this->getVerbosity()) {
            return;
        }

        $messages = (array)$messages;

        foreach ($messages as $message) {
            if ($newline) {
                $message .= PHP_EOL;
            }
            $message = preg_replace('/.*\n/', $this->prefix . '$0', $message);

            switch ($type) {
                case self::OUTPUT_NORMAL:
                    $message = $this->getFormatter()->format($message);
                    break;
                case self::OUTPUT_RAW:
                    break;
                case self::OUTPUT_PLAIN:
                    $message = strip_tags($this->getFormatter()->format($message));
                    break;
                default:
                    throw new \InvalidArgumentException(sprintf('Unknown output type given (%s)', $type));
            }

            $this->doWrite($message, false);
        }
    }


    /**
     * Set the prefix.
     *
     * @param string $prefix
     * @return void
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }


    /**
     * Returns the current prefix.
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }
}