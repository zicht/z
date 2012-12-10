<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Tool\Task\Env;

use Zicht\Tool\Task\Task;

class Ssh extends Task
{
    static function uses()
    {
        return array(
            'environment'
        );
    }


    function execute()
    {
        $this->context->exec('ssh -t ' . $this->context->get('ssh') . ' "cd ' . $this->context->get('root') . '; bash"');
    }


    function simulate()
    {
        $this->context->exec('ssh ' . $this->context->get('ssh'));
    }
}