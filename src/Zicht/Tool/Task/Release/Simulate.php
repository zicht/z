<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Task\Release;

use Zicht\Tool\Task\Task;

class Simulate extends Deploy
{
    static function uses()
    {
        return array(
            'environment',
            'sync.src'
        );
    }


    function execute()
    {
        $this->context->writeln("Simulated release to environment " . $this->context->getEnvironment());
    }
}