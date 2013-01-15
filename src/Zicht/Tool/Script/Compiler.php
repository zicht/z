<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Script;

/**
 * Wraps the tokenization, parsing and compiling into one convenience class.
 */
class Compiler
{
    /**
     * Constructor.
     */
    public function __construct()
    {
    }

    /**
     * Compile an input string to PHP code
     *
     * @param string $input
     * @return string
     */
    public function compile($input)
    {
        $buffer = new Buffer();
        $tokenizer = new Tokenizer($input);
        $parser = new Parser();
        $parser->parse(new TokenStream($tokenizer->getTokens()))->compile($buffer);
        $code = $buffer->getResult();
        return $code;
    }
}