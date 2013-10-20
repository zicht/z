<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Script\Parser;

use \Zicht\Tool\Script\Token;
use \Zicht\Tool\Script\Node;
use \Zicht\Tool\Script\Node\Expr\Op;
use \Zicht\Tool\Script\AbstractParser;
use \Zicht\Tool\Script\TokenStream;
/**
 * Expression parser
 */
class Expression extends AbstractParser
{
    /**
     * Unary prefix operators
     *
     * @var array
     */
    public static $PREFIX_UNARY = array('!', '-', '~');

    /**
     * Binary infix operators
     *
     * @var array
     */
    public static $INFIX_BINARY = array(
        '==', '!=', '<=', '>=', '<', '>', '&&', '||',
        'cat' // 'cat' is a hack for BC supporting the dot operator for concatenation
    );


    /**
     * Does a recursive descent parsing of the token stream and returns the resulting node.
     *
     * @param \Zicht\Tool\Script\TokenStream $stream
     * @return \Zicht\Tool\Script\Node\Node
     */
    public function parse(TokenStream $stream)
    {
        // BC feature: if a legacy env token is matched, replace it with envs[target_env]
        if ($stream->match(Token::LEGACY_ENV)) {
            $stream->next();

            if ($stream->match(Token::OPERATOR, '.')) {
                $ret = new Node\Expr\Subscript(new Node\Expr\Variable('envs'));
                $ret->append(new Node\Expr\Variable('target_env'));
            } else {
                $ret = new Node\Expr\Variable('target_env');
            }
        } elseif ($stream->match(Token::OPERATOR, array('!', '-'))) {
            $value = $stream->current()->value;
            $stream->next();
            $ret = new Op\Unary($value, $this->parse($stream));
        } elseif ($stream->match(Token::IDENTIFIER)) {
            $name = $stream->current()->value;
            $stream->next();

            $ret = new Node\Expr\Variable($name);
        } elseif ($stream->match(Token::STRING)) {
            $ret = new Node\Expr\Str($stream->current()->value);
            $stream->next();
        } elseif ($stream->match(Token::NUMBER)) {
            $ret = new Node\Expr\Number($stream->current()->value);
            $stream->next();
        } elseif ($stream->match(Token::OPERATOR, '(')) {
            $stream->next();
            $ret = new Node\Expr\Parens($this->parse($stream));
            $stream->expect(Token::OPERATOR, ')');
        } elseif ($stream->match(Token::OPERATOR, '[')) {
            $stream->next();
            $ret = new Node\Expr\ListNode();
            if (!$stream->match(Token::OPERATOR, ']')) {
                $ret->append($this->parse($stream));
                while ($stream->match(',')) {
                    $stream->next();
                    $ret->append($this->parse($stream));
                }
            }
            $stream->expect(Token::OPERATOR, ']');
        } else {
            $this->err($stream);
            return null;
        }

        if ($stream->valid()) {
            while ($stream->valid() && $stream->match(Token::OPERATOR, array('(', '.', '['))) {
                $type = $stream->current();

                $stream->next();
                if ($type->value === '(') {
                    $ret = new Node\Expr\Call($ret);
                    if (!$stream->match(Token::OPERATOR, ')')) {
                        do {
                            $arg = $this->parse($stream);
                            $ret->append($arg);

                            if ($stream->match(',')) {
                                $stream->next();
                            } else {
                                break;
                            }
                        } while (true);
                    }
                    $stream->expect(Token::OPERATOR, ')');
                } else {
                    $ret = new Node\Expr\Subscript($ret);

                    if ($type->value === '.') {
                        $token = $stream->expect(Token::IDENTIFIER);
                        $ret->append(new Node\Expr\Str($token->value));
                    } else {
                        $ret->append($this->parse($stream));
                    }

                    switch($type->value) {
                        case '[':
                            $stream->expect(Token::OPERATOR, ']');
                            break;
                    }
                }
            }
        }

        if ($stream->valid()) {
            if ($stream->match(Token::OPERATOR, self::$INFIX_BINARY)) {
                $value = $stream->current()->value;
                $stream->next();
                $ret = new Op\Binary($value, $ret, $this->parse($stream));
            } elseif ($stream->match(Token::OPERATOR, '?')) {
                $stream->next();
                if ($stream->match(Token::OPERATOR, ':')) {
                    $then = null;
                } else {
                    $then = $this->parse($stream);
                }

                if ($stream->valid() && $stream->match(Token::OPERATOR, ':')) {
                    $stream->next();
                    $else = $this->parse($stream);
                } else {
                    $else = null;
                }
                $ret = new Op\Ternary('?', $ret, $then, $else);
            }
        }

        // little syntactic sugar for function calls without parentheses:
        $allowInlineCallTokens = array(Token::IDENTIFIER, Token::STRING, Token::NUMBER, Token::LEGACY_ENV);
        if ($stream->valid() && ($stream->match($allowInlineCallTokens))) {
            $ret = new Node\Expr\Call($ret);
            $ret->append($this->parse($stream));
        }

        return $ret;
    }
}