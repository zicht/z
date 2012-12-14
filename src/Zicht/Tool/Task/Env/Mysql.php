<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Tool\Task\Env;

use Zicht\Tool\Task\Task;
use Zicht\Tool\Context;

class Mysql extends Task
{
    static function uses()
    {
        return array(
            'environment'
        );
    }


    function execute()
    {
        $this->context->execScript('ssh $(ssh) -t "mysql $(db)"', Context::EXEC_PASSTHRU);
    }


    function simulate()
    {
        $this->context->execScript('ssh $(ssh) "mysql $(db) -Ne\'SHOW TABLES\'"');
    }
}