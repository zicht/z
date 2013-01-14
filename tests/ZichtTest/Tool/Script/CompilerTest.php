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


class CompilerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider cases
     */
    function testParser($input, $result)
    {
        $tokenizer = new Tokenizer($input);
        $parser = new Parser(new TokenStream($tokenizer->getTokens()));
        $root = $parser->parse();
        $compiler = new \Zicht\Tool\Script\Buffer();
        $root->compile($compiler);
        $this->assertEquals($result, $compiler->getResult());
    }


    /**
     *
     */
    function cases()
    {
        return array(
            array('', ''),
            array('$(w00t)', '$z[\'w00t\']'),
            array('a $(w00t) b', "'a ' . \$z['w00t'] . ' b'"),
            array('a $(w00t) b', "'a ' . \$z['w00t'] . ' b'"),
//            array('$(w00t())',array('type' => 'Script', 'nodes' => array(array('type' => 'Expr\\Func')))),
//            array('$(w00t(w00t))',array('type' => 'Script', 'nodes' => array(array('type' => 'Expr\\Func', 'nodes' => array(array('type' => 'Expr\\Variable')))))),
//            array('$(w00t(foo, bar))',array('type' => 'Script', 'nodes' => array(array('type' => 'Expr\\Func', 'nodes' => array(array('type' => 'Expr\\Variable'), array('type' => 'Expr\\Variable')))))),
//            array('$( w00t(w00t()) )',array('type' => 'Script', 'nodes' => array(array('type' => 'Expr\\Func', 'nodes' => array(array('type' => 'Expr\\Func')))))),
//            array('$( w00t(foo(), bar()) )',array('type' => 'Script', 'nodes' => array(array('type' => 'Expr\\Func', 'nodes' => array(array('type' => 'Expr\\Func'), array('type' => 'Expr\\Func')))))),
        );
    }
}