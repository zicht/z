<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Tool\Command;

use Symfony\Component\Console\Application as SymfonyApp;
use Zicht\Tool\Application as ZApp;
use Zicht\Tool\Command\BaseCommand;
use Zicht\Tool\Container\Container;

class CommandImpl extends BaseCommand
{
    public function doGetContainer()
    {
        return $this->getContainer();
    }
}

class BaseCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \UnexpectedValueException
     */
    public function testFailureIfNotLocalApplicationImplementation()
    {
        $command = new CommandImpl('test');
        $command->setApplication(new SymfonyApp());
        $command->doGetContainer();
    }

    public function testNoFailureIfAppIsZ()
    {
        $command = new CommandImpl('test');
        $container = new Container();
        $app = new ZApp('The Zicht Tool');
        $app->setContainer($container);

        $command->setApplication($app);
        $this->assertEquals($container, $command->doGetContainer());
    }
}