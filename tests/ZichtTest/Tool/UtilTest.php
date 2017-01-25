<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace ZichtTest\Tool;

class UtilTest extends \PHPUnit_Framework_TestCase
{
    function testToPhp()
    {
        $this->assertEquals('1', \Zicht\Tool\Util::toPhp(1));
        $this->assertEquals("'str'", \Zicht\Tool\Util::toPhp('str'));
        $this->assertEquals("array(1, 1, 1)", \Zicht\Tool\Util::toPhp(array(1, 1, 1)));
        $this->assertEquals("array(1, array(2, 2, 2), 1)", \Zicht\Tool\Util::toPhp(array(1, array(2, 2, 2), 1)));
        $this->assertEquals("array('a' => 1, 'b' => 2)", \Zicht\Tool\Util::toPhp(array('a' => 1, 'b' => 2)));
    }


    function testTypeOf()
    {
        $this->assertEquals("string", \Zicht\Tool\Util::typeof('str'));
        $this->assertEquals("integer", \Zicht\Tool\Util::typeof(1));
        $this->assertEquals("array", \Zicht\Tool\Util::typeof(array()));
        $this->assertEquals("stdClass", \Zicht\Tool\Util::typeof(new \stdClass));
        $this->assertEquals(__CLASS__, \Zicht\Tool\Util::typeof($this));
    }
}
