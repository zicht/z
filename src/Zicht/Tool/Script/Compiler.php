<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Script;

class Compiler
{
    function __construct()
    {
    }

    public function compile($input)
    {
        $compiler = new \Zicht\Tool\Script\Buffer();
        $tokenizer = new \Zicht\Tool\Script\Tokenizer($input);
        $parser = new \Zicht\Tool\Script\Parser(new \Zicht\Tool\Script\TokenStream($tokenizer->getTokens()));
        $parser->parse()->compile($compiler);
        $code = $compiler->getResult();
        return $code;
    }
}