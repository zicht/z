<?php

namespace Zicht\Tool\Container;

use Symfony\Component\Console\Output\OutputInterface;
use Exception;

class ConfigurationException extends \RuntimeException implements VerboseException
{
    public function __construct($message = "", $code = 0, Exception $previous = null, $config)
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