<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Script\Tokenizer;

use \Zicht\Tool\Script\Token;
use \Zicht\Tool\Script\TokenizerInterface;

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

            if (preg_match('/^env(?=(?:\.(root|web|url|db|ssh)|\b))/', $substr, $m)) {
                trigger_error(
                    "As of version 1.1, using 'env' for referencing environments configuration is deprecated. "
                    . "Use envs[target_env] instead",
                    E_USER_DEPRECATED
                );
                $ret[]= new Token(Token::LEGACY_ENV);
                $needle += strlen($m[0]);
            } elseif (preg_match('/^(==|<=?|>=?|!=?|\?|:|\|\||&&|xor|or|and|\.|\[|\]|\(|\))/', $substr, $m)) {
                if ($m[0] == '.' && end($ret)->type == Token::WHITESPACE) {
                    trigger_error(
                        "As of version 1.1, using the dot-operator for concatenation is deprecated. "
                        . "Please use cat() or sprintf() in stead",
                        E_USER_DEPRECATED
                    );
                    $ret[]= new Token(Token::OPERATOR, 'cat');
                    $needle += strlen($m[0]);
                    continue;
                }

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
            } elseif ($string{$needle} == '"') {
                $strData = '';

                $escape = false;
                for ($j = $needle +1; $j < $len; $j ++) {
                    $ch = $string{$j};

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
