<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Script;

use \Zicht\Tool\Script\Tokenizer\Expression as ExpressionTokenizer;

/**
 * Tokenizer for the script language
 */
class Tokenizer implements TokenizerInterface
{
    /**
     * Constructor.
     */
    public function __construct()
    {
    }


    /**
     * Returns an array of tokens
     *
     * @param string $string
     * @param int &$needle
     * @throws \UnexpectedValueException
     * @return array
     */
    public function getTokens($string, &$needle = 0)
    {
        $exprTokenizer = new ExpressionTokenizer();
        $ret = array();
        $depth = 0;
        $needle = 0;
        $len = strlen($string);
        while ($needle < $len) {
            $before = $needle;
            $substr = substr($string, $needle);
            if ($depth === 0) {
                if (preg_match('/^(\$|\?)\(/', $substr, $m)) {
                    $needle += strlen($m[0]);
                    $ret[]= new Token(Token::EXPR_START, $m[0]);
                    $depth ++;
                } else {
                    $token = end($ret);

                    if (preg_match('/^\$\$\(/', $substr, $m)) {
                        $value = substr($m[0], 1);
                        $needle += strlen($m[0]);
                    } else {
                        $value = $string{$needle};
                        $needle += strlen($value);
                    }
                    if ($token && $token->match(Token::DATA)) {
                        $token->value .= $value;
                        unset($token);
                    } else {
                        $ret[]= new Token(Token::DATA, $value);
                    }
                }
            } else {
                $ret = array_merge($ret, $exprTokenizer->getTokens($string, $needle));
                $depth = 0;
            }
            if ($before === $needle) {
                // safety net.
                throw new \UnexpectedValueException(
                    "Unexpected input near token {$string{$needle}}, unsupported character"
                );
            }
        }
        return $ret;
    }
}
