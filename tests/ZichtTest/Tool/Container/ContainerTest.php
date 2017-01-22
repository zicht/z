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
        $this->container->output = $this->createMock('Symfony\Component\Console\Output\OutputInterface');
    }

    /**
     * @covers \Zicht\Tool\Container\Container::__construct
     */
    public function testThatConstructionInitializesDefaults()
    {
        $this->setUp();

        foreach (array('sprintf', 'mtime', 'ctime', 'atime', 'cat', 'keys', 'str', 'is_file', 'is_dir') as $function) {
            $functionSpec = $this->container->get($function);
            $this->assertTrue(is_callable($functionSpec[0]), "Checking for {$function}");
        }

        $this->assertTrue(false !== $this->container->has('cwd'), 'Checking for cwd');
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
        $this->container->set('VERBOSE', true);
        $this->container->set('FORCE', true);
        $this->container->set('EXPLAIN', true);
        $this->assertEquals('--force --verbose --explain', $this->container->value($this->container->resolve(array('z', 'opts'))));
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


    public function testCmdForwardsToTaskIfPrefixedWithAtSign()
    {
        $this->container->set(array('tasks', 'foo'), function() {
            return 'waa';
        });
        $this->assertEquals('waa', $this->container->cmd('@foo'));
    }

    public function testCmdForwardsToExecIfNotPrefixedWithAtSign()
    {
        $exec = $this->getMockBuilder('Zicht\Tool\Container\Executor')->setMethods(array('execute'))->disableOriginalConstructor()->getMock();
        $container = new Container($exec);
        $exec->expects($this->once())->method('execute')->with('hello');
        $container->cmd('hello');
    }

    public function testExecutorDoesNotGetCalledIfExplainIsUsed()
    {
        $exec = $this->getMockBuilder('Zicht\Tool\Container\Executor')->setMethods(array('execute'))->disableOriginalConstructor()->getMock();
        $container = new Container($exec);
        $container->set(array('EXPLAIN'), true);
        $exec->expects($this->never())->method('execute');
        $container->cmd('hello');
    }

    public function testHelperExecAlwaysCallsExecutor()
    {
        foreach (array(true, false) as $explain) {
            $exec = $this->getMockBuilder('Zicht\Tool\Container\Executor')->setMethods(array('execute'))->disableOriginalConstructor()->getMock();
            $container = new Container($exec);
            $container->set(array('EXPLAIN'), $explain);
            $exec->expects($this->once())->method('execute');
            $container->helperExec('hello');
        }
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
        $this->assertEquals('1', $this->container->value($this->container->resolve('foo')));
        $this->assertEquals('1', $this->container->value($this->container->resolve('foo')));
    }

    /**
     * @covers \Zicht\Tool\Container\Container::decl
     */
    public function testDeclareWillPassContainer()
    {
        $this->container->decl('foo', function($c) {
            return get_class($c);
        });
        $this->assertEquals('Zicht\Tool\Container\Container', $this->container->value($this->container->resolve('foo')));
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
     * @covers \Zicht\Tool\Container\Container::lookup
     * @expectedException \OutOfBoundsException
     */
    public function testLookupWillFailIfRequiredButNotFound4()
    {
        $this->container->lookup(array('a' => array('b' => 'value')), array(1), true);
    }

    /**
     * @covers \Zicht\Tool\Container\Container::lookup
     * @expectedException \InvalidArgumentException
     */
    public function testLookupWillFailIfPathIsEmpty()
    {
        $this->container->lookup(array('a' => array('b' => 'value')), array(), true);
    }

    /**
     * @covers \Zicht\Tool\Container\Container::resolve
     */
    public function testResolve()
    {
        $this->container->set(array('a', 'b'), 'value');
        $this->assertEquals('value', $this->container->resolve(array('a', 'b')));
    }


    /**
     * @covers \Zicht\Tool\Container\Container::resolve
     * @covers \Zicht\Tool\Container\Container::path
     */
    public function testResolveCompat()
    {
        set_error_handler(function(){}, E_USER_DEPRECATED);
        $this->container->set(array('a.b'), 'value');
        $this->assertEquals($this->container->resolve(array('a', 'b')), $this->container->resolve('a.b'));
        restore_error_handler();
    }


    /**
     * @covers \Zicht\Tool\Container\Container::resolve
     * @covers \Zicht\Tool\Container\Container::path
     */
    public function testLookupCompat()
    {
        set_error_handler(function(){}, E_USER_DEPRECATED);
        $this->container->set(array('a.b'), 'value');
        $this->assertEquals($this->container->lookup($this->container->getValues(), 'a.b'), $this->container->resolve('a.b'));
        restore_error_handler();
    }


    /**
     * @expectedException \Zicht\Tool\Container\CircularReferenceException
     */
    public function testCircularReferenceResolutionWillFail()
    {
        $this->container->set('a', function($c) {
            return $c->resolve('b');
        });
        $this->container->set('b', function($c) {
            return $c->resolve('a');
        });
        $this->container->resolve('a');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testExceptionsAreConvertedToRuntimeExceptions()
    {
        $this->container->set('a', function() {
            throw new \Exception("Any");
        });
        $this->container->resolve('a');
    }
}
