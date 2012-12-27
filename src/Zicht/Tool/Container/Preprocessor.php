<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Tool\Container;


class Preprocessor
{
    function __construct() {
    }


    function preprocess($config) {
        foreach ($config['tasks'] as $i => $task) {
            $config['tasks'][$i] = new Task($task);
        }
        return $config;
    }
}