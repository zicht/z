<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Tool\Task\Release;

use Zicht\Tool\Task\Task;

class Build extends Task
{
    function getDepends()
    {
        return array(
            'versioning.export'
        );
    }

    function execute()
    {
        $this->context->chdir($this->context->get('build.dir'));
        foreach ($this->context->get('build.commands') as $command) {
            $this->context->exec($command);
        }
        $this->context->popdir();
        $this->context->set('deploy.src', rtrim(realpath($this->context->get('build.dir')), '/') . '/');
    }
}