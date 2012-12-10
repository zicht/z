<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Tool\Task;

interface TaskInterface
{
    function setExecutionContext(ContextInterface $context);
    function execute();
    function simulate();
    function getDepends();
    function getPriority();
    function setName($name);
    function getName();
}