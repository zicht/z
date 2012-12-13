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
        $this->context->execScript('ssh -t $(ssh) "cd $(root); bash"', \Zicht\Tool\Context::EXEC_PASSTHRU);
    }


    function simulate()
    {
        $this->context->exec('ssh ' . $this->context->get('ssh'), \Zicht\Tool\Context::EXEC_PASSTHRU);
    }
}