<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Task\Release;

use Zicht\Tool\Task\Task;

class Deploy extends Task
{
    function getDepends() {
        return array(
//            'release.tag',
            'release.build',
            'transport.sync',
        );
    }


    function execute()
    {
        echo "Released to environment " . $this->context->getEnvironment() . "\n";
    }
}