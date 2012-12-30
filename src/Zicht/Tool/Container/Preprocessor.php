<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Tool\Container;

/**
 * Preprocessor for processing a Z config and replacing nodes with compilable nodes where necessary
 */
class Preprocessor
{
    /**
     * Stubbed
     */
    public function __construct()
    {
    }


    /**
     * Preprocess the configuration
     *
     * @param array $config
     * @return array mixed
     */
    public function preprocess($config)
    {
        foreach ($config['tasks'] as $i => $task) {
            $config['tasks'][$i] = new Task($task);
        }
        return $config;
    }
}