<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Tool\Task\Content;

use \Zicht\Tool\Task\Task;

class Push extends Task
{
    static function uses() {
        return array(
            'backup.file',
            'environment'
        );
    }


    function execute()
    {
        $this->context->execScript('rsync --progress $(backup.file) $(ssh):$(root)$(backup.file)');
        $this->context->execScript('ssh $(ssh) "cd $(root) && tar zxvf $(backup.file)"');
        $this->context->execScript('ssh $(ssh) "mysql $(db) < db.sql"');
    }


    function simulate()
    {
        $this->context->writeln('rsync --progress $(backup.file) $(ssh):$(root)$(backup.file)');
        $this->context->writeln('ssh $(ssh) "cd $(root) && tar zxvf $(backup.file)"');
        $this->context->writeln('ssh $(ssh) "mysql $(db) < db.sql"');
    }


    function getRemoteCommand()
    {
        return 'cd $(root); mysqldump -Q --opt $(db) > db.sql; tar zcvf $(backup.file) $(content.dir) db.sql; rm db.sql';
    }
}