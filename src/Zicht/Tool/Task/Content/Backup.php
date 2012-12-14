<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Task\Content;

use \Zicht\Tool\Task\Task;

class Backup extends Task
{
    static function uses()
    {
        return array(
            'environment'
        );
    }


    static function provides()
    {
        return array(
            'backup.file'
        );
    }


    function execute()
    {
        $this->context->set('backup.file', $this->context->get('environment') . '-' . date('YmdHis') . '.tar.gz');
        $this->context->execScript(
            sprintf(
                'ssh $(ssh) "%s"',
                $this->getRemoteCommand()
            )
        );
        $this->context->execScript(
            'rsync --progress $(ssh):$(root)$(backup.file) ./$(backup.file)'
        );
        $this->context->writeln(sprintf('%s written', $this->context->get('backup.file')));
        $this->context->execScript(
            'ssh $(ssh) "rm $(root)/$(backup.file)"'
        );
    }


    function simulate()
    {
        $this->context->set('backup.file', '[backup filename]');
        $this->context->writeln("Would remotely execute command \n\t{$this->getRemoteCommand()}\nand sync file to local backup");
    }


    function getRemoteCommand()
    {
        return 'cd $(root); mysqldump -Q --opt $(db) > db.sql; tar zcvf $(backup.file) $(content.dir) db.sql; rm db.sql';
    }
}