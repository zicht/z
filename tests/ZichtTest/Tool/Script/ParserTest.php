<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace ZichtTest\Tool\Script;

use \Zicht\Tool\Script\Tokenizer;
use \Zicht\Tool\Script\TokenStream;
use \Zicht\Tool\Script\Parser;
use \Zicht\Tool\Script\Dumper;


class ParserTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider cases
     */
    function testParser($input, $tree)
    {
        $tokenizer = new Tokenizer($input);
        $parser = new Parser();
        $root = $parser->parse(new TokenStream($tokenizer->getTokens()));
        $dumper = new Dumper();
        $this->assertEquals($tree, $dumper->getAst($root));
    }


    /**
     *
     */
    function cases()
    {
        return array(
            array('', array('type' => 'Script')),
            array('$(w00t)', array('type' => 'Script', 'nodes' => array(array('type' => 'Expr\Expr', 'nodes' => array(array('type' => 'Expr\\Variable')))))),
            array('$(w00t waaa)', array('type' => 'Script', 'nodes' => array(array('type' => 'Expr\Expr', 'nodes' => array(array('type' => 'Expr\\Func', 'nodes' => array(array('type' => 'Expr\Variable')))))))),
            array('$(1 ? 2)', array('type' => 'Script', 'nodes' => array(array('type' => 'Expr\Expr', 'nodes' => array(array('type' => 'Expr\\Op\\Ternary', 'nodes' => array(array('type' => 'Expr\Number'), array('type' => 'Expr\\Number'), null))))))),
            array('$(1 ? 2 : 3)', array('type' => 'Script', 'nodes' => array(array('type' => 'Expr\Expr', 'nodes' => array(array('type' => 'Expr\\Op\\Ternary', 'nodes' => array(array('type' => 'Expr\Number'), array('type' => 'Expr\\Number'), array('type' => 'Expr\\Number')))))))),
            array('$(1 ?: 3)', array('type' => 'Script', 'nodes' => array(array('type' => 'Expr\Expr', 'nodes' => array(array('type' => 'Expr\\Op\\Ternary', 'nodes' => array(array('type' => 'Expr\Number'), null, array('type' => 'Expr\\Number')))))))),
        );
    }
}