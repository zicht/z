<?php

namespace ZichtTest\Tool;

use Zicht\Tool\Parser;
use Zicht\Tool\Script\Buffer;
use Zicht\Tool\Script\Node\Node;
use Zicht\Tool\Script\Node\NodeInterface;

class ParserTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
    }

    /**
     * @test
     * @dataProvider cases
     */
    public function test($z, $php)
    {
        $parser = new Parser($z, file_get_contents($z));
        $this->assertEquals(include $php, $this->fold($parser->parse(file_get_contents($z, 'r'))));
    }


    /**
     * @test
     * @dataProvider current
     */
    public function testCurrent($z, $php)
    {
        $this->test($z, $php);
    }


    public function cases()
    {
        return array_map(
            function ($zfile) {
                return [$zfile, dirname($zfile) . '/' . basename($zfile, '.z') . '.php'];
            },
            glob(__DIR__ . '/assets/parser/*.z')
        );
    }


    public function current()
    {
        return [
            [__DIR__ . '/assets/parser/cur.z', __DIR__ . '/assets/parser/cur.php'],
        ];
    }


    private function fold(NodeInterface $node)
    {
        $compiler = new Buffer();
        $node->compile($compiler);

        $_ = null;
        eval('$_ = ' . $compiler->getResult() . ';');

        return $_;
    }
}