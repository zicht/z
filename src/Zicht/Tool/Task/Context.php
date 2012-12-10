<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Tool\Task;

use Symfony\Component\DependencyInjection\ContainerInterface;

class Context implements ContextInterface
{
    protected $context = array();
    protected $dirStack = array();
    protected $selectedEnvironment = null;

    function __construct(ContainerInterface $container, $options, $environments) {
        $this->container = $container;
        foreach ($options as $option => $value) {
            $this->set($option, $value);
        }
        $this->environments = $environments;
    }


    function setEnvironment($name) {
        $this->selectedEnvironment = $name;
    }

    function getEnvironment() {
        return $this->selectedEnvironment;
    }

    function set($name, $value) {
        $this->context[$name] = $value;
    }

    function get($name) {
        if ($this->selectedEnvironment) {
            if (isset($this->environments[$this->selectedEnvironment][$name])) {
                return $this->environments[$this->selectedEnvironment][$name];
            }
        }
        return $this->context[$name];
    }


    function getService($name) {
        return $this->container->get($name);
    }

    function chdir($dir) {
        $this->dirStack[]= getcwd();
        if (!@chdir($dir)) {
            array_pop($this->dirStack);
            throw new \UnexpectedValueException("Could not change to dir {$dir}");
        }
    }

    function mkdir($dir) {
        mkdir($dir, 0777 | umask(), true);
        if (!is_dir($dir)) {
            throw new \UnexpectedValueException("Could not create dir {$dir}");
        }
    }


    function popdir() {
        chdir(array_pop($this->dirStack));
    }


    function exec($cmd) {
        echo "[EXEC] {$cmd}\n";
        passthru($cmd);
    }


    function writeln($ln) {
        echo $ln, "\n";
    }
}