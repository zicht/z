<?php

namespace ZichtTest\Tool;

use Zicht\Tool\Parser;

class ParserTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->parser = new Parser();
    }

    /**
     * @test
     * @dataProvider cases
     */
    public function test($z, $php)
    {
        $this->assertEquals(include $php, $this->parser->parse(file_get_contents($z, 'r')));
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
}