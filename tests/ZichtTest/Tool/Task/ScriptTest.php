<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Tool\Task;

use Zicht\Tool\Context;
use Zicht\Tool\Script;

/**
 * @covers \Zicht\Tool\Task\Script
 */
class ScriptTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider scripts
     */
    function testScript($in, $vars, $expect)
    {
        $context = new Context($this->getMock('Symfony\Component\DependencyInjection\ContainerInterface'), $vars);
        $script = new Script($in);
        $this->assertEquals($expect, $script->evaluate($context));
    }


    function scripts() {
        return array(
            array('echo $(some.var)', array('some' => array('var' => 'w00t')), 'echo w00t'),
            array('echo $$(some.var)', array('some' => array('var' => 'w00t')), 'echo $(some.var)'),
            array('echo $(some.var)', array('some' => array('var' => array('foo', 'bar'))), 'echo foo bar'),
//            array('echo $(some.var)', array('some' => array('var' => array('$(foo)', '$(bar)')), 'foo' => 'qux', 'bar' => 'baz'), 'echo qux baz')
        );
    }
}