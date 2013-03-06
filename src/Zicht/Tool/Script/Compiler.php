<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Script;

use Zicht\Tool\Util;

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


    public function parse($input)
    {
        if (strlen($input) == 0) {
            return null;
        }
        return $this->parser->parse(new TokenStream($this->tokenizer->getTokens($input)));
    }

    /**
     * Compile an input string to PHP code
     *
     * @param string $input
     * @return string
     */
    public function compile($input)
    {
        if (strlen($input) == 0) {
            return null;
        }
        try {
            $buffer = new Buffer();
            $this->parse($input)->compile($buffer);
            $code = $buffer->getResult();
            return $code;
        } catch(\UnexpectedValueException $e) {
            throw new \UnexpectedValueException('Error while compiling input: ' . Util::toPhp($input), 0, $e);
        }
    }
}