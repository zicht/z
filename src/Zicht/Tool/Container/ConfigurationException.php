<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Container;

use Symfony\Component\Console\Output\OutputInterface;
use Exception;

/**
 * Exception wrapper for more verbosity in the config.
 */
class ConfigurationException extends \RuntimeException implements VerboseException
{
    /**
     * @{inheritDoc}
     */
    public function __construct($message = "", $code = 0, Exception $previous = null, array $config = null)
    {
        parent::__construct($message, $code, $previous);
        $this->config = $config;
    }

    /**
     * @{inheritDoc}
     */
    public function output(OutputInterface $output)
    {
        ob_start();
        var_dump($this->config);
        $dmp = ob_get_clean();

        $output->writeln("Source configuration:\n" . $dmp);
    }
}
