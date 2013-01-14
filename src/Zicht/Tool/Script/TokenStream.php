<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */
namespace Zicht\Tool\Script;

class TokenStream
{
    protected $ptr = -1;

    /**
     * @param Token[] $tokenList
     */
    function __construct($tokenList, $skipWhitespace = true)
    {
        if ($skipWhitespace) {
            $this->tokenList = array_values(array_filter($tokenList, function($token) {
                return !$token->match(Token::WHITESPACE);
            }));
        } else {
            $this->tokenList = $tokenList;
        }
    }


    function next()
    {
        $this->ptr ++;
    }


    function hasNext()
    {
        return $this->ptr < count($this->tokenList) -1;
    }

    function valid()
    {
        return $this->ptr < count($this->tokenList);
    }

    function current()
    {
        if (!isset($this->tokenList[$this->ptr])) {
            var_dump($this->tokenList);
            throw new \UnexpectedValueException("Unexpected input at offset {$this->ptr}");
        }
        return $this->tokenList[$this->ptr];
    }


    function match($type, $value = null)
    {
        return $this->current()->match($type, $value);
    }


    function expect($type, $value = null)
    {
        if (!$this->match($type, $value)) {
            throw new \UnexpectedValueException("Unexpected token {$this->current()->type}, expected {$type}");
        }
        $this->next();
        return true;
    }
}