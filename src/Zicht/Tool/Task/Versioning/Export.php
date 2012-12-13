<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Tool\Task\Versioning;

use Zicht\Tool\Task\Task;

class Export extends Task
{
    function execute() {
        /** @var $version \Zicht\Tool\Versioning\Svn\Versioning */
        if (is_dir($this->context->get('build.dir'))) {
            $this->context->execScript('rm -rf $(build.dir)');
        }
        $version = $this->context->getService('versioning');
        $version->export($this->context->get('build.version', false), $this->context->get('build.dir'));
    }
}