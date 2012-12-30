<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Tool;

use Zicht\Tool\Container\Container;
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
        $context = new Container($vars);
        $script = new Script($in);
        $this->assertEquals($expect, $script->evaluate($context));
    }


    function scripts() {
        return array(
            array('echo $(some.var)', array('some.var' => 'w00t'), 'echo w00t'),
            array('echo $$(some.var)', array('some.var' => 'w00t'), 'echo $(some.var)'),
            array('echo $(some.var)', array('some.var' => array('foo', 'bar')), 'echo foo bar'),
            array('echo $(some.var)', array('some.var' => function() { return 'w00t'; }), 'echo w00t'),
//            array('echo $(some.var())', array('some.var' => function() { return function() { return 'w00t'; }; }), 'echo w00t'),
        );
    }
}