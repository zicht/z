<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Task\Transport;

use Zicht\Tool\Task\Task;

class Sync extends Task
{
    static function uses()
    {
        return array(
            'sync.src'
        );
    }


    function execute()
    {
        $this->execRsync($this->getRsyncOptions());
    }


    function simulate()
    {
        $options = $this->getRsyncOptions();
        $options[]= '--dry-run';
        $this->execRsync($options);
    }


    function execRsync($options)
    {
        $this->context->exec(
            'rsync ' . join(' ', $options)
        );
    }


    function getRsyncOptions() {
        $src = $this->context->get('sync.src');

        $options = array(
            '-rupv', '--size-only', '--delete',
            '--backup-dir=../rsync-backups'
        );

        if (is_file($excludeList = ($src . 'exclude-list.txt'))) {
            $options[]= '--exclude-from=' . $excludeList;
        }
        $options[]= escapeshellarg($this->context->get('sync.src'));
        $options[]= escapeshellarg($this->context->get('ssh')) . ':' . escapeshellarg($this->context->get('root'));
        return $options;
    }
}