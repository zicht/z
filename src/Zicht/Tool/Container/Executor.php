<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht online <http://zicht.nl>
 */

namespace Zicht\Tool\Container;

use \Symfony\Component\Process\Process;

/**
 * Runs the commands in the shell.
 */
class Executor
{
    /**
     * Constructor
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }


    /**
     * Executes the passed command line in the shell.
     *
     * @param string $cmd
     * @param null &$captureOutput
     * @return int
     *
     * @throws ExecutionAbortedException
     * @throws \UnexpectedValueException
     */
    public function execute($cmd, &$captureOutput = null)
    {
        $ret = 0;
        if ($this->container->resolve('interactive')) {
            passthru($cmd, $ret);
        } else {
            $process = $this->createProcess();
            $process->setStdin($cmd);
            if (null !== $captureOutput) {
                $process->run(function($type, $data) use(&$captureOutput) {
                    $captureOutput .= $data;
                });
            } else {
                $process->run(array($this, 'processCallback'));
            }
            $ret = $process->getExitCode();
        }
        if ($ret != 0) {
            if ($ret == Container::ABORT_EXIT_CODE) {
                throw new ExecutionAbortedException("Command '$cmd' was aborted");
            } else {
                throw new \UnexpectedValueException("Command '$cmd' failed with exit code {$ret}");
            }
        }
        return $ret;
    }


    /**
     * Create the process instance to use for non-interactive handling
     *
     * @return \Symfony\Component\Process\Process
     */
    protected function createProcess()
    {
        $process = new Process($shell = $this->container->get('SHELL'));
        $process->setTimeout($this->container->get('TIMEOUT'));
        return $process;
    }


    /**
     * The callback used for the process executed by Process
     *
     * @param mixed $mode
     * @param string $data
     * @return void
     */
    public function processCallback($mode, $data)
    {
        if (isset($this->container->output)) {
            $this->container->output->write($data);
        }
    }
}