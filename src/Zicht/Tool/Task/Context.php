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
        $this->setOptions($options);
        $this->setEnvironments($environments);
    }

    function setOptions($options) {
        foreach ($options as $option => $value) {
            $this->set($option, $value);
        }
    }

    function setEnvironments($environments) {
        $this->environments = $environments;
    }


    function setEnvironment($name) {
        $this->selectedEnvironment = $name;
    }

    function getEnvironment() {
        return $this->selectedEnvironment;
    }

    function set($name, $value) {
        $path = explode('.', $name);
        $ptr =& $this->context;
        foreach (array_slice($path, 0, -1) as $element) {
            if (!isset($ptr[$element])) {
                $ptr[$element] = array();
            }
            $ptr =& $ptr[$element];
        }
        $ptr[end($path)] = $value;
    }

    function get($name, $require = true) {
        $path = explode('.', $name);
        $ptr = $this->context;
        foreach ($path as $element) {
            if (!isset($ptr[$element])) {
                $ptr = null;
                break;
            }
            $ptr =& $ptr[$element];
        }
        if ($ptr === null && $this->selectedEnvironment) {
            $ptr = $this->environments[$this->selectedEnvironment];
            foreach ($path as $element) {
                if (!isset($ptr[$element])) {
                    $ptr = null;
                    break;
                }
                $ptr =& $ptr[$element];
            }
        }

        if ($require && $ptr === null) {
            throw new \InvalidArgumentException("Context value {$name} is not found");
        }

        return $ptr;
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
        $status = null;
        passthru($cmd, $status);
        if ($status !== 0) {
            throw new \RuntimeException("Executing command failed with status code $status");
        }
    }


    function writeln($ln) {
        echo $ln, "\n";
    }
}