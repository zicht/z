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
     */
    public function __construct()
    {
    }


    /**
     * Returns an array of tokens
     *
     * @return array
     * @throws \UnexpectedValueException
     */
    public function getTokens($string, &$i = 0)
    {
        $exprTokenizer = new \Zicht\Tool\Script\Tokenizer\Expression();
        $ret = array();
        $depth = 0;
        $i = 0;
        $len = strlen($string);
        while ($i < $len) {
            $before = $i;
            $substr = substr($string, $i);
            if ($depth === 0) {
                if (preg_match('/^(\$|\?)\(/', $substr, $m)) {
                    $i += strlen($m[0]);
                    $ret[]= new Token(Token::EXPR_START, $m[0]);
                    $depth ++;
                } else {
                    $token =& end($ret);

                    if (preg_match('/^\$\$\(/', $substr, $m)) {
                        $value = substr($m[0], 1);
                        $i += strlen($m[0]);
                    } else {
                        $value = $string{$i};
                        $i += strlen($value);
                    }
                    if ($token && $token->match(Token::DATA)) {
                        $token->value .= $value;
                        unset($token);
                    } else {
                        $ret[]= new Token(Token::DATA, $value);
                    }
                }
            } else {
                $ret = array_merge($ret, $exprTokenizer->getTokens($string, $i));
                $depth = 0;
            }
            if ($before === $i) {
                // safety net.
                throw new \UnexpectedValueException("Unexpected input near token {$string{$i}}");
            }
        }
        return $ret;
    }
}
