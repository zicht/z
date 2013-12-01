<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */
namespace ZichtTest\Tool\Container;

use \Zicht\Tool\Container\Task;
use \Zicht\Tool\Container\Definition;
use \Zicht\Tool\Container\Declaration;
use \Zicht\Tool\Script\Node;

/**
 * @covers Zicht\Tool\Container\ContainerBuilder
 */
class ContainerBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider builds
     */
    function testBuild($input, $output, $isExprCallback = false)
    {
        $builder = new \Zicht\Tool\Container\ContainerBuilder($input);
        if ($isExprCallback) {
            $builder->addExpressionPath($isExprCallback);
        }

        $this->assertEquals($output, $builder->build()->nodes);
    }
    function builds()
    {
        return array(
            array(array(), array()),
            array(array('tasks' => array()), array()),
            array(
                array('tasks' => array('a' => array())),
                array(new Task(array('tasks', 'a'), array()))
            ),
            array(
                array('tasks' => array('a' => array('set' => array('foo' => '"bar"')))),
                array(new Task(array('tasks', 'a'), array('set' => array('foo' => new Node\Task\ArgNode('foo', new Node\Expr\Str("bar"), false)))))
            ),
            array(
                array('tasks' => array('a' => array('set' => array('foo' => '? bar')))),
                array(new Task(array('tasks', 'a'), array('set' => array('foo' => new Node\Task\ArgNode('foo', new Node\Expr\Variable("bar"), true)))))
            ),
            array(
                array('tasks' => array('a' => array('unless' => 'foo'))),
                array(new Task(array('tasks', 'a'), array('unless' => new Node\Expr\Variable("foo"))))
            ),
            array(
                array('tasks' => array('a' => array('assert' => 'foo'))),
                array(new Task(array('tasks', 'a'), array('assert' => new Node\Expr\Variable("foo"))))
            ),
            array(
                array('tasks' => array('a' => array('pre' => array('foo')))),
                array(new Task(array('tasks', 'a'), array('pre' => array(new Node\Script(array(new Node\Expr\Data('foo')))))))
            ),
            array(
                array('tasks' => array('a' => array('yield' => 'foo'))),
                array(new Task(array('tasks', 'a'), array('yield' => new Node\Expr\Variable("foo"))))
            ),
            array(
                array('some_config' => array('a' => 'foo')),
                array(new Definition(array('some_config', 'a'), 'foo'))
            ),
            array(
                array('some_config' => array('a' => 'foo')),
                array(new Declaration(array('some_config', 'a'), new Node\Expr\Variable('foo'))),
                function ($path) {
                    return end($path) === 'a';
                }
            ),
        );
    }
}