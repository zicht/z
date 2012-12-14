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
            'backup.input',
            'environment'
        );
    }


    function execute()
    {
        $this->context->execScript('rsync --progress $(backup.input) $(ssh):$(root)$(backup.input)');
        $this->context->execScript('ssh $(ssh) "cd $(root) && tar zxvf $(backup.input)"');
        $this->context->execScript('ssh $(ssh) "mysql $(db) < db.sql"');
    }


    function simulate()
    {
        $this->context->writeln('rsync --progress $(backup.input) $(ssh):$(root)$(backup.input)');
        $this->context->writeln('ssh $(ssh) "cd $(root) && tar zxvf $(backup.input)"');
        $this->context->writeln('ssh $(ssh) "mysql $(db) < db.sql"');
    }


    function getRemoteCommand()
    {
        return 'cd $(root); mysqldump -Q --opt $(db) > db.sql; tar zcvf $(backup.input) $(content.dir) db.sql; rm db.sql';
    }
}