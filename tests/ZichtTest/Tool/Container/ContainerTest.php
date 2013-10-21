<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2013 Gerard van Helden <http://melp.nl>
 */
namespace ZichtTest\Tool\Container;

use \Zicht\Tool\Container\Task;
use Zicht\Tool\Container\Container;
use \Zicht\Tool\Container\Definition;
use \Zicht\Tool\Container\Declaration;
use \Zicht\Tool\Script\Node;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Zicht\Tool\Container\Container::__construct
     */
    public function testThatConstructionInitializesDefaults()
    {
        $container = new Container();

        foreach (array('sprintf', 'mtime', 'ctime', 'atime', 'cat', 'keys', 'str', 'is_file', 'is_dir') as $function) {
            $functionSpec = $container->get($function);
            $this->assertTrue(is_callable($functionSpec[0]));
        }

        $this->assertTrue(false !== $container->has('cwd'));
        $this->assertTrue(false !== $container->has('users'));
    }


    public function testConcatenationFunction()
    {
        $container = new Container();
        $this->assertEquals('abc', $container->call($container->resolve('cat'), 'a', 'b', 'c')));
    }


    public function testZOpts()
    {
        $container = new Container();
        $container->set('verbose', true);
        $container->set('force', true);
        $container->set('explain', true);
        $this->assertEquals('--verbose --force --explain', $container->resolve(array('z', 'opts')));
    }
}