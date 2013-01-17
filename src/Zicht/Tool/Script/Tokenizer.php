<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Script;

/**
 * Tokenizer for the script language
 */
class Tokenizer
{
    /**
     * Construct the tokenizer with the given input string.
     *
     * @param string $string
     */
    public function __construct($string)
    {
        $this->string = $string;
    }


    /**
     * Returns an array of tokens
     *
     * @return array
     * @throws \UnexpectedValueException
     */
    public function getTokens()
    {
        $ret = array();
        $depth = 0;
        $i = 0;
        $len = strlen($this->string);
        while ($i < $len) {
            $before = $i;
            $substr = substr($this->string, $i);
            if ($depth === 0) {
                if (preg_match('/^\$\(/', $substr, $m)) {
                    $i += strlen($m[0]);
                    $ret[]= new Token(Token::EXPR_START, $m[0]);
                    $depth ++;
                } elseif (preg_match('/^\!\(/', $substr, $m)) {
                    $i += strlen($m[0]);
                    $ret[]= new Token(Token::EXPR_START, $m[0]);
                    $depth ++;
                } else {
                    $token =& end($ret);
                    if ($token && $token->match(Token::DATA)) {
                        $token->value .= $this->string{$i};
                        unset($token);
                    } else {
                        $ret[]= new Token(Token::DATA, $this->string{$i});
                    }
                    $i ++;
                }
            } else {
                if (preg_match('/^[\w.]+/', $substr, $m)) {
                    $ret[] = new Token(Token::IDENTIFIER, $m[0]);
                    $i += strlen($m[0]);
                } elseif (preg_match('/^\s+/', $substr, $m)) {
                    $ret[]= new Token(Token::WHITESPACE, $m[0]);
                    $i += strlen($m[0]);
                } elseif (preg_match('/^([0-9]*.)?[0-9]+/', $substr, $m)) {
                    $ret[]= new Token(Token::NUMBER, $m[0]);
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
                } elseif (preg_match('/^[\?,]/', $substr, $m)) {
                    $ret[] = new Token($m[0]);
                    $i ++;
                } elseif ($this->string{$i} == '"') {
                    $strData = '';
                    $escape = false;
                    for ($j = $i +1; $j < $len; $j ++) {
                        $ch = $this->string{$j};

                        if ($ch == '\\') {
                            $escape = true;
                        } elseif ($ch == '"') {
                            if ($escape) {
                                $escape = false;
                            } else {
                                $j ++;
                                break;
                            }
                        } else {
                            if ($escape) {
                                $strData .= '\\';
                                $escape = false;
                            }
                            $strData .= $ch;
                        }
                    }
                    $ret[]= new Token(Token::STRING, $strData);
                    $i = $j;
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
