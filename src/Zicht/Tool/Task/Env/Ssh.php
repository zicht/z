<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Tool\Task\Env;

use Zicht\Tool\Task\Task;

class Ssh extends Task
{
    function execute()
    {
        $this->context->exec('ssh -t ' . $this->context->get('ssh') . ' "cd ' . $this->context->get('deploy.target') . '; bash"');
    }
}