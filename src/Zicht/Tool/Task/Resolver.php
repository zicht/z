<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Tool\Task;

class Resolver implements ResolverInterface
{
    function __construct($namespaces = array())
    {
        $this->namespaces = $namespaces;
        $this->namespaces[]= __NAMESPACE__;
    }


    function resolve($name)
    {
        $classNames = array();
        foreach ($this->namespaces as $namespace) {
            $className = $this->formatClassName($namespace, $name);
            $classNames[]= $className;
            if (class_exists($className)) {
                return $className;
            }
        }
        throw new TaskResolutionError($name, $classNames);
    }


    function formatClassName($namespace, $name) {
        return $namespace
            . '\\'
            . ucfirst(
                preg_replace_callback(
                    '/(?:\b|[_-])([a-z])/',
                    function($n) {
                        return ucfirst($n[1]);
                    },
                    str_replace('.', '\\', $name)
                )
            );
    }
}