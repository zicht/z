<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Tool\Task;

interface BuilderInterface
{
    /**
     * @return \Zicht\Tool\Task\TaskInterface
     */
    function build($name);
}