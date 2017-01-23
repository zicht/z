<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Tool\Container\Command\Descriptor;


use Symfony\Component\Console\Input\InputOption;
use Zicht\Tool\Command\Descriptor\TextDescriptor;

class TextDescriptorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TextDescriptor
     */
    private $test;

    public function setUp()
    {
        $this->test = new TextDescriptor();
    }

    /**
     * @dataProvider hiddenOptNames
     */
    public function testIsHidden($optName, $expectsHidden = true)
    {
        $mock = $this->createMock(InputOption::class);
        $mock->expects($this->once())->method('getName')->will($this->returnValue($optName));
        $this->assertEquals($expectsHidden, $this->test->isHiddenOption($mock));
    }


    public function hiddenOptNames()
    {
        return [
            ['help'],
            ['version'],
            ['verbose'],
            ['quiet'],
            ['explain'],
            ['force'],
            ['plugin'],
            ['debug'],
            ['something-else', false]
        ];
    }
}