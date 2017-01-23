<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Tool\Script;

use Zicht\Tool\Script\Buffer;

class BufferTest extends \PHPUnit_Framework_TestCase
{
    public function testWrite()
    {
        $buffer = new Buffer();
        $buffer->write('foo');
        $this->assertEquals('foo', $buffer->getResult());
    }

    public function testSubsequentWrite()
    {
        $buffer = new Buffer();
        $buffer->write('foo')->write('foo');
        $this->assertEquals('foofoo', $buffer->getResult());
    }

    public function testWriteln()
    {
        $buffer = new Buffer();
        $buffer->writeln('foo')->writeln('foo');
        $this->assertEquals("foo\nfoo\n", $buffer->getResult());
    }


    public function testIndentation()
    {
        $buffer = new Buffer();
        $buffer->indent(1);
        $buffer->writeln('foo')->writeln('foo');
        $this->assertEquals("    foo\n    foo\n", $buffer->getResult());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testUnderflowIndent()
    {
        $buffer = new Buffer();
        $buffer->indent(-1);
    }


    public function testDedentation()
    {
        $buffer = new Buffer();
        $buffer->indent(1);
        $buffer->writeln('foo')->writeln('foo');
        $buffer->indent(-1);
        $buffer->writeln('foo')->writeln('foo');

        $this->assertEquals("    foo\n    foo\nfoo\nfoo\n", $buffer->getResult());
    }
}