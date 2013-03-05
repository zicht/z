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
    public function __construct($parser = null, $tokenizer = null)
    {
        $this->tokenizer = (null === $tokenizer ? new Tokenizer() : $tokenizer);
        $this->parser = (null === $parser ? new Parser() : $parser);
    }

    /**
     * Compile an input string to PHP code
     *
     * @param string $input
     * @return string
     */
    public function compile($input)
    {
        try {
            $buffer = new Buffer();
            $node = $this->parser->parse(new TokenStream($this->tokenizer->getTokens($input)));
            $node->compile($buffer);
            $code = $buffer->getResult();
            return $code;
        } catch(\UnexpectedValueException $e) {
            throw new \UnexpectedValueException('Error while compiling input: ' . var_export($input, true), 0, $e);
        }
    }
}