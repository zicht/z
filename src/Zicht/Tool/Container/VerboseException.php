<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */
namespace Zicht\Tool\Container;

use \Symfony\Component\Console\Output\OutputInterface;

/**
 * Verbose exceptions can explain themselves more verbose to an output.
 */
interface VerboseException
{
    /**
     * Write verbose explanation of the exception to the output
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $o
     * @return void
     */
    public function output(OutputInterface $o);
}