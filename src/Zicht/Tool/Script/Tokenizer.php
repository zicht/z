<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Tool\Script;

use Zicht\Tool\Script\Exception\TokenException;
use Zicht\Tool\Script\Tokenizer\Expression as ExpressionTokenizer;

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
     * @throws TokenException
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
                // match either '$(' or '@(' and mark that as an EXPR_START token.
                if (preg_match('/^([$@])\(/', $substr, $m)) {
                    $needle += strlen($m[0]);
                    $ret[] = new Token(Token::EXPR_START, $m[0]);

                    // record expression depth, to make sure the usage of parentheses inside the expression doesn't
                    // break tokenization (e.g. '$( ("foo") )'
                    $depth++;
                } else {
                    // store the current token in a temp var for appending, in case it's a DATA token
                    $token = end($ret);

                    // handle escaping of the $( syntax, '$$(' becomes '$('
                    if (preg_match('/^\$\$\(/', $substr, $m)) {
                        $value = substr($m[0], 1);
                        $needle += strlen($m[0]);
                    } else {
                        $value = $string[$needle];
                        $needle += strlen($value);
                    }

                    // if the current token is DATA, and the previous token is DATA, append the value to the previous
                    // and ignore the current.
                    if ($token && $token->match(Token::DATA)) {
                        $token->value .= $value;
                        unset($token);
                    } else {
                        $ret[] = new Token(Token::DATA, $value);
                    }
                }
            } else {
                $ret = array_merge($ret, $exprTokenizer->getTokens($string, $needle));
                $depth = 0;
            }
            if ($before === $needle) {
                // safety net.
                throw new TokenException(
                    "Unexpected input near token {$string[$needle]}, unsupported character"
                );
            }
        }
        return $ret;
    }
}
