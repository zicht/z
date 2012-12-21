<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Tool\Context;

class ParameterBagTest extends \PHPUnit_Framework_TestCase
{
    public $bag;

    function setUp()
    {
        $this->bag = new \Zicht\Tool\Context\ParameterBag();
    }


    /**
     * @dataProvider data
     */
    function testPaths($path, $root = null, $expect = null, $value = null)
    {
        $value = ($value === null ? rand() : $value);
        $this->bag->setPath($path, $value);
        $this->assertEquals($value, $this->bag->getPath($path));
        if ($root !== null) {
            $this->assertEquals($expect, $this->bag->getPath($root));
        }
    }


    function testSettingPathWillNotOverride() {
        $this->bag->setPath('a.b.c', 'w00t');
        $this->bag->setPath('a.b.d', 'waa');
        $this->assertEquals(array(
            'b' => array(
                'c' => 'w00t',
                'd' => 'waa'
            )
        ), $this->bag->get('a'));
    }


    function data() {
        return array(
            array('a'),
            array('a.b'),
            array('a.b.c'),
            array('a.b.c', 'a', array('b' => array('c' => 'w00t')), 'w00t'),
            array('a.b.c', 'a.b', array('c' => 'w00t'), 'w00t'),
            array('a.b.c', 'a.b', array('c' => array('x' => 'y')), arraY('x' => 'y'))
        );
    }
}