<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
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