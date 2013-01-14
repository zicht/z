<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Script;


class Tokenizer
{
    function __construct($string)
    {
        $this->string = $string;
    }


    function getTokens()
    {
        $ret = array();
        $depth = 0;
        for ($i = 0; $i < strlen($this->string);) {
            $before = $i;
            if ($depth === 0) {
                if (preg_match('/^\$\(/', substr($this->string, $i), $m)) {
                    $i += strlen($m[0]);
                    $ret[]= new Token(Token::EXPR_START, $m[0]);
                    $depth ++;
                } elseif (preg_match('/^\!\(/', substr($this->string, $i), $m)) {
                    $i += strlen($m[0]);
                    $ret[]= new Token(Token::EXPR_START, $m[0]);
                    $depth ++;
                } else {
                    if ($token = array_pop($ret)) {
                        if ($token->match(Token::DATA)) {
                            $token->value .= $this->string{$i};
                        } else {
                            array_push($ret, $token);
                            $token = new Token(Token::DATA, $this->string{$i});
                        }
                    } else {
                        $token = new Token(Token::DATA, $this->string{$i});
                    }
                    $i ++;
                    array_push($ret, $token);
                }
            } else {
                if (preg_match('/^[\w.]+/', substr($this->string, $i), $m)) {
                    $ret[] = new Token(Token::IDENTIFIER, $m[0]);
                    $i += strlen($m[0]);
                } elseif (preg_match('/^\s+/', substr($this->string, $i), $m)) {
                    $ret[]= new Token(Token::WHITESPACE, $m[0]);
                    $i += strlen($m[0]);
                } elseif ($this->string{$i} == ')') {
                    $depth --;
                    if ($depth == 0) {
                        $ret[] = new Token(Token::EXPR_END, ')');
                    } else {
                        $ret[] = new Token(')');
                    }
                    $i ++;
                } elseif ($this->string{$i} == '(') {
                    $depth ++;
                    $ret[] = new Token('(');
                    $i ++;
                } elseif (preg_match('/^[\?,]/', substr($this->string, $i), $m)) {
                    $ret[] = new Token($m[0]);
                    $i ++;
                }
            }
            if ($before === $i) {
                // safety net.
                throw new \UnexpectedValueException("Unexpected input near token {$this->string{$i}}");
            }
        }
        return $ret;
    }
}
