<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Task\Transport;

class Simulate extends Sync
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


    function getRsyncOptions() {
        $ret = parent::getRsyncOptions();
        $ret[]= '--dry-run';
        return $ret;
    }
}