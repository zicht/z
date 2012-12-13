<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Tool\Versioning;

use \PHPUnit_Framework_TestCase;
use \Zicht\Tool\Versioning\Resolver;
use Zicht\Tool\Versioning\Version;
 
class ResolverTest extends PHPUnit_Framework_TestCase
{
    function setUp() {
        $this->resolver = new Resolver();
    }


    /**
     * @dataProvider resolutions
     */
    function testResolution($expected, $in) {
        $this->markTestIncomplete("Not implemented yet");
        $this->assertEquals($expected, $this->resolver->resolve($in));
    }

    /**
     *
     */
    function resolutions() {
        return array(
            array(
                array(new Version(Version::BRANCH, null, null), null),
                array(new Version(Version::BRANCH, '2.1', null), '2.1'),
                array(new Version(Version::TAG, 'piet', null), 'piet'),
            )
        );
    }
}