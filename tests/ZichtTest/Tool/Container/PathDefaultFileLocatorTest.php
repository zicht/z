<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */
namespace ZichtTest\Tool\Container;

use Zicht\Tool\Configuration\PathDefaultFileLocator;

class PathDefaultFileLocatorTest extends \PHPUnit_Framework_TestCase
{
    function testEnvPath()
    {
        $loader = new PathDefaultFileLocator('PATH', array('a', 'b', 'c'));
        $this->assertEquals('/bin/bash', $loader->locate('bash'));
    }


    function testDefaultPath()
    {
        $this->assertFalse(getenv('some bogus variable'), "testing precondition 'some bogus variable' is indeed undefined");

        $loader = new PathDefaultFileLocator('some bogus variable', array('/bin'));
        $this->assertEquals('/bin/bash', $loader->locate('bash'));
    }
}