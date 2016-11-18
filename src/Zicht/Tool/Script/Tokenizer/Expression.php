<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Script\Tokenizer;

use Zicht\Tool\Script\Token;
use Zicht\Tool\Script\TokenizerInterface;

/**
 * Tokenizer for expressions
 */
class Expression implements TokenizerInterface
{
    /**
     * @{inheritDoc}
     */
    public function getTokens($string, &$needle = 0)
    {
        $depth = 1;
        $ret = array();
        $len = strlen($string);
        while ($needle < $len) {
            $substr = substr($string, $needle);
            $before = $needle;

            if (preg_match('/^(==|=~|<=?|>=?|!=?|\?|:|\|\||&&|xor\b|or\b|and\b|\.|\[|\]|\{|\}|\(|\)|\+|-|\/|\*)/', $substr, $m)) {
                if ($m[0] == ')') {
                    $depth --;
                    if ($depth == 0) {
                        $ret[] = new Token(Token::EXPR_END, ')');
                        $needle ++;
                        break;
                    }
                } elseif ($m[0] === '(') {
                    $depth ++;
                }
                $ret[]= new Token(Token::OPERATOR, $m[0]);
                $needle += strlen($m[0]);
            } elseif (preg_match('/^(true|false|in|as|null)\b/', $substr, $m)) {
                $ret[] = new Token(Token::KEYWORD, $m[0]);
                $needle += strlen($m[0]);
            } elseif (preg_match('/^[a-z_][\w]*/i', $substr, $m)) {
                $ret[] = new Token(Token::IDENTIFIER, $m[0]);
                $needle += strlen($m[0]);
            } elseif (preg_match('/^\s+/', $substr, $m)) {
                $ret[]= new Token(Token::WHITESPACE, $m[0]);
                $needle += strlen($m[0]);
            } elseif (preg_match('/^([0-9]*.)?[0-9]+/', $substr, $m)) {
                $ret[]= new Token(Token::NUMBER, $m[0]);
                $needle += strlen($m[0]);
            } elseif (preg_match('/^[\?,]/', $substr, $m)) {
                $ret[] = new Token($m[0]);
                $needle ++;
            } elseif ($string{$needle} == '"' || $string{$needle} == "'") {
                $strData = '';
                $start = $string{$needle};

                $escape = false;
                for ($j = $needle +1; $j < $len; $j ++) {
                    $ch = $string{$j};

                    if ($ch == '\\') {
                        $escape = true;
                    } elseif ($ch == $start) {
                        if ($escape) {
                            $escape = false;
                        } else {
                            $j ++;
                            break;
                        }
                    } else {
                        if ($escape) {
                            switch ($ch) {
                                case 'n':
                                    $strData .= "\n";
                                    break;
                                case '\\':
                                    $strData .= "\\";
                                    break;
                                default:
                                    $strData .= '\\' . $ch;
                                    break;
                            }
                            $escape = false;
                        } else {
                            $strData .= $ch;
                        }
                    }
                }
                $ret[]= new Token(Token::STRING, $strData);
                $needle = $j;
            }
            if ($before === $needle) {
                // safety net.
                throw new \UnexpectedValueException(
                    "Unexpected input near token {$string{$needle}}, unsupported character ($string)"
                );
            }
        }
        return $ret;
    }
}
