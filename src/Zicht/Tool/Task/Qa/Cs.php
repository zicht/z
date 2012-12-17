<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Tool\Task\Qa;

use Zicht\Tool\Task\Task;

class Cs extends Task
{
    function execute()
    {
        $this->context->execScript('phpcs src --standard=$(qa.standard)');
    }


    function simulate()
    {
        $this->context->writeln('Would execute phpcs');
    }
}