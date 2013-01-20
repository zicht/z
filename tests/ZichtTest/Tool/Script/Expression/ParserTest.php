<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace ZichtTest\Tool\Script\Expression;

use \Zicht\Tool\Script\Tokenizer\Expression as Tokenizer;
use \Zicht\Tool\Script\Parser\Expression as Parser;
use \Zicht\Tool\Script\TokenStream;
use \Zicht\Tool\Script\Dumper;


class ParserTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider cases
     */
    function testParser($input, $tree)
    {
        $tokenizer = new Tokenizer();
        $parser = new Parser();
        $root = $parser->parse(new TokenStream($tokenizer->getTokens($input)));
        $dumper = new Dumper();
        $this->assertEquals($tree, $dumper->getAst($root));
    }


    /**
     *
     */
    function cases()
    {
        return array(
            array('w00t', array('type' => 'Expr\\Variable')),
        );
    }
}