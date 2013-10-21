<?php
/**
 * Created by JetBrains PhpStorm.
 * User: gerard
 * Date: 10/21/13
 * Time: 10:47 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Zicht\Tool\Container;

use \Symfony\Component\Process\Process;

class Executor
{
    public function __construct(Container $container)
    {
        $this->container = $container;
    }


    public function execute($cmd)
    {
        $ret = 0;
        if ($this->container->resolve('interactive')) {
            passthru($cmd, $ret);
        } else {
            $process = $this->createProcess();
            $process->setStdin($cmd);
            $process->run(array($this, 'processCallback'));
            $ret = $process->getExitCode();
        }
        if ($ret != 0) {
            throw new \UnexpectedValueException("Command '$cmd' failed with exit code {$ret}");
        }
        return $ret;
    }


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