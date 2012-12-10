<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Tool\Task\Release;

use Zicht\Tool\Task\Task;

class Build extends Task
{
    static function provides()
    {
        return array(
            'sync.src'
        );
    }


    function execute()
    {
        $this->context->chdir($this->context->get('build.dir'));
        foreach ($this->options['post'] as $command) {
            $this->context->exec($command);
        }
        $this->context->popdir();
        $this->context->set('sync.src', rtrim(realpath($this->context->get('build.dir')), '/') . '/');
    }


    function simulate()
    {
        $this->context->set('sync.src', getcwd());
    }
}