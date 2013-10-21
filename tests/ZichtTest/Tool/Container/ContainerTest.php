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

/**
 * @property Container $container
 */
class ContainerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->container = new Container();
    }

    /**
     * @covers \Zicht\Tool\Container\Container::__construct
     */
    public function testThatConstructionInitializesDefaults()
    {
        $this->setUp();

        foreach (array('sprintf', 'mtime', 'ctime', 'atime', 'cat', 'keys', 'str', 'is_file', 'is_dir') as $function) {
            $functionSpec = $this->container->get($function);
            $this->assertTrue(is_callable($functionSpec[0]));
        }

        $this->assertTrue(false !== $this->container->has('cwd'));
        $this->assertTrue(false !== $this->container->has('users'));
    }


    public function testGetValues()
    {
        $this->container->set('foo', 'bar');

        $values = $this->container->getValues();
        $this->assertEquals('bar', $values['foo']);
    }


    /**
     * @covers \Zicht\Tool\Container\Container::__construct
     */
    public function testConcatenationFunction()
    {
        $this->assertEquals('abc', $this->container->call($this->container->resolve('cat'), 'a', 'b', 'c'));
    }


    /**
     * @covers \Zicht\Tool\Container\Container::__construct
     */
    public function testZOpts()
    {
        $this->container->set('verbose', true);
        $this->container->set('force', true);
        $this->container->set('explain', true);
        $this->assertEquals('--force --verbose --explain', $this->container->resolve(array('z', 'opts')));
    }

    /**
     * @covers \Zicht\Tool\Container\Container::get
     * @covers \Zicht\Tool\Container\Container::set
     */
    public function testGetAndSetWithStringPath()
    {

        $this->container->set('foo', 'value');
        $this->assertEquals('value', $this->container->get('foo'));
    }


    /**
     * @covers \Zicht\Tool\Container\Container::get
     * @covers \Zicht\Tool\Container\Container::set
     */
    public function testGetAndSetWithArrayPath()
    {

        $this->container->set(array('foo', 'bar'), 'value');
        $this->assertEquals('value', $this->container->get(array('foo', 'bar')));
    }


    /**
     * @covers \Zicht\Tool\Container\Container::str
     */
    public function testStrWillConcatenateArrayBashStyle()
    {

        $this->assertEquals('a b c', $this->container->str(array('a', 'b', 'c')));
    }


    /**
     * @covers \Zicht\Tool\Container\Container::str
     * @expectedException \UnexpectedValueException
     */
    public function testStrWillFailConcatenateIfArrayIsNested()
    {

        $this->container->str(array('b' => array('c' , 'd')));
    }


    /**
     * @covers \Zicht\Tool\Container\Container::value
     */
    public function testValueWillCallClosure()
    {

        $this->assertEquals('foo', $this->container->value(function () {
            return 'foo';
        }));
    }


    public function testListener()
    {
        $params = array();
        $this->container->subscribe(function () use(&$params) {
            $params = func_get_args();
        });
        $this->container->notify('foo', 'bar');

        $this->assertEquals('foo', $params[0]);
        $this->assertEquals('bar', $params[1]);
        $this->assertEquals($this->container, $params[2]);
    }


    public function testCmdForwardsToTaskIfPrefixedWithAtSign()
    {
        $this->container->set(array('tasks', 'foo'), function() {
            return 'waa';
        });
        $this->assertEquals('waa', $this->container->cmd('@foo'));
    }


    /**
     * @covers \Zicht\Tool\Container\Container::decl
     */
    public function testDeclareWillExecuteFunctionOnlyOnce()
    {
        $i = 0;

        $this->container->decl('foo', function() use(&$i) {
            return ++$i;
        });
        $this->assertEquals('1', $this->container->resolve('foo'));
        $this->assertEquals('1', $this->container->resolve('foo'));
    }

    /**
     * @covers \Zicht\Tool\Container\Container::decl
     */
    public function testDeclareWillPassContainer()
    {
        $this->container->decl('foo', function($c) {
            return get_class($c);
        });
        $this->assertEquals('Zicht\Tool\Container\Container', $this->container->resolve('foo'));
    }


    /**
     * @covers \Zicht\Tool\Container\Container::lookup
     */
    public function testLookup()
    {
        $this->assertEquals('value', $this->container->lookup(array('a' => array('b' => 'value')), array('a', 'b')));
    }

    /**
     * @covers \Zicht\Tool\Container\Container::lookup
     * @expectedException \OutOfBoundsException
     */
    public function testLookupWillFailIfRequiredButNotFound()
    {
        $this->container->lookup(array('a' => array('b' => 'value')), array('a', 'c'), true);
    }

    /**
     * @covers \Zicht\Tool\Container\Container::lookup
     * @expectedException \OutOfBoundsException
     */
    public function testLookupWillFailIfRequiredButNotFound2()
    {
        $this->container->lookup(array('a' => array('b' => 'value')), array('a', 'b', 'c'), true);
    }

    /**
     * @covers \Zicht\Tool\Container\Container::lookup
     * @expectedException \OutOfBoundsException
     */
    public function testLookupWillFailIfRequiredButNotFound3()
    {
        $this->container->lookup(array('a' => array('b' => 'value')), array('b'), true);
    }

    /**
     * @covers \Zicht\Tool\Container\Container::resolve
     */
    public function testResolve()
    {
        $this->container->set(array('a', 'b'), 'value');
        $this->assertEquals('value', $this->container->resolve(array('a', 'b')));
    }
}