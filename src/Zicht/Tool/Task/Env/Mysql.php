<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Tool\Task\Env;

use Zicht\Tool\Task\Task;

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
        $this->context->exec('ssh ' . $this->context->get('ssh') . ' -t \"mysql\"');
    }


    function simulate()
    {
        $this->context->exec('ssh ' . $this->context->get('ssh') . ' \"mysql -Ne\'SHOW TABLES\'\"');
    }
}