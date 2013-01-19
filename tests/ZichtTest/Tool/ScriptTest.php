<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Tool;

use \Zicht\Tool\Container\Container;
use \Zicht\Tool\Script;

/**
 * ScriptTest
 *
 * @covers \Zicht\Tool\Script
 */
class ScriptTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests the parsing/compiling of variables
     *
     * @param string $in
     * @param array $vars
     * @param string $expect
     * @return void
     *
     * @dataProvider scripts
     */
    public function testScript($in, $vars, $expect)
    {
        $context = new Container($vars);
        $script = new Script($in);
        $this->assertEquals($expect, $script->evaluate($context));
    }


    /**
     * Provides test data for testScript()
     *
     * @return array
     */
    public function scripts()
    {
        $fn = function() {
            return 'w00t';
        };
        return array(
            array('echo $(some.var)', array('some.var' => 'w00t'), 'echo w00t'),
            array('echo $$(some.var)', array('some.var' => 'w00t'), 'echo $(some.var)'),
            array('echo $(some.var)', array('some.var' => array('foo', 'bar')), 'echo foo bar'),
            array('echo $(some.var)', array('some.var' => $fn), 'echo w00t'),
        );
    }
}