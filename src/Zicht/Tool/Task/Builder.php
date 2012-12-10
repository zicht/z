<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Task;

use ReflectionClass;
 
class Builder implements BuilderInterface
{
    function __construct(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }


    function build($name, $options = array())
    {
        $impl = new ReflectionClass($this->resolver->resolve($name));
        /** @var $ret TaskInterface */
        $ret = $impl->newInstance($name, $options);
        if (! ($ret instanceof TaskInterface)) {
            throw new \UnexpectedValueException("The task rsolver returned a class with name {$name}, but it is no instance of TaskInterface");
        }
        $ret->setName($name);
        return $ret;
    }
}