<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Container;

use Symfony\Component\Console\Output\OutputInterface;

class ExecutionAbortedException extends \RuntimeException implements VerboseException
{
    public function output(OutputInterface $output)
    {
        $output->writeln('<comment>Aborted</comment>');
        if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
            $output->writeln($this->getMessage());
        }
    }
}