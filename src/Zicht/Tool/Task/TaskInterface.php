<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Tool\Task;

use Zicht\Tool\ContextInterface;

interface TaskInterface
{
//    static function uses();
//    static function provides();

    function setExecutionContext(ContextInterface $context);
    function execute();
    function simulate();
    function getDepends();
    function getName();
}