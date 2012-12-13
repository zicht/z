<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Tool;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Zicht\Tool\Context\ParameterBag;

class Context implements ContextInterface
{
    const EXEC_PASSTHRU = 1;
    const EXEC_PROGRESS = 2;
    const EXEC_REPORT   = 3;

    protected $context = array();
    protected $dirStack = array();
    protected $selectedEnvironment = null;
    protected $environments = array();

    function __construct(ContainerInterface $container, $options = array(), $environments = array())
    {
        $this->container = $container;
        $this->options = new ParameterBag();
        $this->setOptions($options);
        $this->setEnvironments($environments);
    }

    function setOptions($options)
    {
        foreach ($options as $option => $value) {
            $this->options->setPath($option, $value);
        }
    }

    function setEnvironments($environments)
    {
        foreach ($environments as $env => $options) {
            $this->environments[$env] = new ParameterBag();
            foreach ($options as $name => $value) {
                $this->environments[$env]->setPath($name, $value);
            }
        }
    }


    function setEnvironment($name)
    {
        $this->selectedEnvironment = $name;
        $this->options->set('environment', $name);
    }

    function getEnvironment()
    {
        return $this->selectedEnvironment;
    }


    function set($name, $value)
    {
        $this->options->setPath($name, $value);
    }


    function get($name, $require = true)
    {
        $ret = null;
        if ($this->selectedEnvironment) {
            try {
                $ret = $this->environments[$this->selectedEnvironment]->getPath($name);
            } catch(\Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException $e) {
            }
        }

        if (null === $ret) {
            $ret = $this->options->getPath($name);
        }

        return $ret;
    }


    function getService($name)
    {
        return $this->container->get($name);
    }


    function chdir($dir)
    {
        $this->dirStack[]= getcwd();
        if (!@chdir($dir)) {
            array_pop($this->dirStack);
            throw new \UnexpectedValueException("Could not change to dir {$dir}");
        }
    }


    function mkdir($dir)
    {
        mkdir($dir, 0777 | umask(), true);
        if (!is_dir($dir)) {
            throw new \UnexpectedValueException("Could not create dir {$dir}");
        }
    }


    function popdir()
    {
        chdir(array_pop($this->dirStack));
    }


    function exec($cmd, $mode = self::EXEC_REPORT)
    {
        switch ($mode) {
            case self::EXEC_PASSTHRU:
                passthru($cmd);
                break;
            case self::EXEC_PROGRESS:
            case self::EXEC_REPORT:
                $proc = new \Symfony\Component\Process\Process($cmd);
                $streamFirst = true;
                $status = $proc->run(function($type, $line) use($mode, &$streamFirst) {
                    if ($streamFirst) {
                        $streamFirst = false;
                        echo "   ";
                    }
                    switch($mode) {
                        case Context::EXEC_REPORT:
                            echo str_replace("\n", "\n   ", $line);
                            break;
                        case Context::EXEC_PROGRESS:
                            echo ".";
                            break;
                    }
                });
                if ($status !== 0) {
                    throw new \RuntimeException("Executing command failed with status code $status");
                }
                echo "\n";
                break;
            default:
                throw new \InvalidArgumentException("invalid mode {$mode}");
        }
    }


    function execScript($script, $passthru = self::EXEC_REPORT)
    {
        $script = new Script($script);
        $this->exec($script->evaluate($this), $passthru);
    }


    function writeln($ln)
    {
        echo $ln, "\n";
    }
}