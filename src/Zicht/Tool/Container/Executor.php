<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht online <http://zicht.nl>
 */

namespace Zicht\Tool\Container;

use Symfony\Component\Process\Process;

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
     * @param string $captureOutput
     * @return int
     *
     * @throws ExecutionAbortedException
     * @throws \UnexpectedValueException
     */
    public function execute($cmd, &$captureOutput = null)
    {
        $isInteractive = $this->container->get('INTERACTIVE');
        $process = $this->createProcess($isInteractive);

        if ($isInteractive) {
            $process->setCommandLine(sprintf('/bin/bash -c \'%s\'', $cmd));
        } else {
            $process->setInput($cmd);
        }

        if (null !== $captureOutput && false === $isInteractive) {
            $process->run(function ($type, $data) use(&$captureOutput) {
                $captureOutput .= $data;
            });
        } else {
            $process->run(array($this, 'processCallback'));
        }

        $ret = $process->getExitCode();

        if ($ret != 0) {
            if ((int)$ret == Container::ABORT_EXIT_CODE) {
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
     * @var     bool $interactive
     * @return \Symfony\Component\Process\Process
     */
    protected function createProcess($interactive = false)
    {
        $process = new Process($this->container->resolve('SHELL'), null, null, null, null, []);

        if ($interactive) {
            $process->setTty(true);
        } else {
            if ($this->container->has('TIMEOUT') && $timeout = $this->container->get('TIMEOUT')) {
                $process->setTimeout($this->container->get('TIMEOUT'));
            }
        }

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
