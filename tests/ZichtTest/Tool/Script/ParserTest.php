<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace ZichtTest\Tool\Script;

use Zicht\Tool\Script\Tokenizer;
use Zicht\Tool\Script\TokenStream;
use Zicht\Tool\Script\Parser;


class ParserTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider cases
     */
    function testParser($input, $tree)
    {
        $tokenizer = new Tokenizer($input);
        $parser = new Parser(new TokenStream($tokenizer->getTokens()));
        $root = $parser->parse();
        $dumper = new \Zicht\Tool\Script\Dumper();
        $this->assertEquals($tree, $dumper->getAst($root));
    }


    /**
     *
     */
    function cases()
    {
        return array(
            array('', array('type' => 'Script')),
            array('$(w00t)', array('type' => 'Script', 'nodes' => array(array('type' => 'Expr\\Variable')))),
            array('$(w00t())',array('type' => 'Script', 'nodes' => array(array('type' => 'Expr\\Func')))),
            array('$(w00t(w00t))',array('type' => 'Script', 'nodes' => array(array('type' => 'Expr\\Func', 'nodes' => array(array('type' => 'Expr\\Variable')))))),
            array('$(w00t(foo, bar))',array('type' => 'Script', 'nodes' => array(array('type' => 'Expr\\Func', 'nodes' => array(array('type' => 'Expr\\Variable'), array('type' => 'Expr\\Variable')))))),
            array('$( w00t(w00t()) )',array('type' => 'Script', 'nodes' => array(array('type' => 'Expr\\Func', 'nodes' => array(array('type' => 'Expr\\Func')))))),
            array('$( w00t(foo(), bar()) )',array('type' => 'Script', 'nodes' => array(array('type' => 'Expr\\Func', 'nodes' => array(array('type' => 'Expr\\Func'), array('type' => 'Expr\\Func')))))),
        );
    }
}