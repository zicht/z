<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Task\Util;
use Zicht\Tool\Task\Task;

class ClearApcCache extends Task
{
    static function uses() {
        return array(
            'environment'
        );
    }

    function execute() {
        $scriptName = rand() . '.php';
        $this->context->execScript(
            sprintf(
                'scp %s $(ssh):$(root)$(web)/%s; wget -O - $(url)%s; ssh $(ssh) "rm $(root)$(web)/%s"',
                dirname(__FILE__) . '/__apc_clear_cache.php',
                $scriptName,
                $scriptName,
                $scriptName
            )
        );
    }
}