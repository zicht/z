<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Script;

/**
 * Wraps a list of tokens into an convenience class for matching and iterating the tokens
 */
class TokenStream
{
    protected $ptr = -1;

    /**
     * Create the tokenstream with the specified list of tokens.
     *
     * @param Token[] $tokenList
     * @param bool $skipWhitespace
     */
    public function __construct($tokenList, $skipWhitespace = true)
    {
        if ($skipWhitespace) {
            $callback = function (Token $token) {
                return !$token->match(Token::WHITESPACE);
            };
            $this->tokenList = array_values(array_filter($tokenList, $callback));
        } else {
            $this->tokenList = $tokenList;
        }
    }


    /**
     * Advances the internal pointer
     *
     * @return void
     */
    public function next()
    {
        $this->ptr ++;
    }

    /**
     * Checks if there is a next token
     *
     * @return bool
     */
    public function hasNext()
    {
        return $this->ptr < count($this->tokenList) -1;
    }


    /**
     * Checks if there is a current tkoen
     *
     * @return bool
     */
    public function valid()
    {
        return $this->ptr < count($this->tokenList);
    }


    /**
     * Returns the current token
     *
     * @return Token
     * @throws \UnexpectedValueException
     */
    public function current()
    {
        if (!isset($this->tokenList[$this->ptr])) {
            var_dump($this->tokenList);
            throw new \UnexpectedValueException("Unexpected input at offset {$this->ptr}");
        }
        return $this->tokenList[$this->ptr];
    }


    /**
     * Checks if the current token matches the specified type and/or value
     *
     * @param string $type
     * @param string $value
     * @return mixed
     */
    public function match($type, $value = null)
    {
        return $this->current()->match($type, $value);
    }


    /**
     * Asserts the current token matches the specified value and/or type. Throws an exception if it doesn't
     *
     * @param string $type
     * @param string $value
     * @return bool
     *
     * @throws \UnexpectedValueException
     */
    public function expect($type, $value = null)
    {
        if (!$this->match($type, $value)) {
            throw new \UnexpectedValueException("Unexpected token {$this->current()->type}, expected {$type}");
        }
        $this->next();
        return true;
    }
}