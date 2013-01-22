<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Script\Parser;

use Zicht\Tool\Script\Token;
use Zicht\Tool\Script\Node;
use Zicht\Tool\Script\Node\Expr\Op;
use Zicht\Tool\Script\AbstractParser;

class Expression extends AbstractParser
{
    function parse(\Zicht\Tool\Script\TokenStream $stream)
    {
        if ($stream->match(Token::OPERATOR, array('!', '-'))) {
            $value = $stream->current()->value;
            $stream->next();
            $ret = new Op\Unary($value, $this->parse($stream));
        } elseif ($stream->match(Token::IDENTIFIER)) {
            $name = $stream->current()->value;
            $stream->next();

            if ($stream->valid()) {
                // little syntactic sugar for function calls without parentheses:
                if ($stream->match(Token::IDENTIFIER) || $stream->match(Token::STRING) || $stream->match(Token::NUMBER)) {
                    $ret = new Node\Expr\Func($name);
                    $ret->append($this->parse($stream));
                } elseif ($stream->match('(')) {
                    $stream->next();
                    $ret = new Node\Expr\Func($name);
                    if (!$stream->match(')')) {
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
                    $stream->expect(')');
                } else {
                    $ret = new Node\Expr\Variable($name);
                }
            } else {
                $ret = new Node\Expr\Variable($name);
            }
        } elseif ($stream->match(Token::STRING)) {
            $ret = new Node\Expr\Str($stream->current()->value);
            $stream->next();
        } elseif ($stream->match(Token::NUMBER)) {
            $ret = new Node\Expr\Number($stream->current()->value);
            $stream->next();
        } elseif ($stream->match('(')) {
            $stream->next();
            $ret = new \Zicht\Tool\Script\Node\Expr\Parens($this->parse($stream));
            $stream->expect(')');
        } else {
            var_dump($stream);
            $this->err($stream);
            return null;
        }

        if ($stream->valid()) {
            if ($stream->match(Token::OPERATOR, array('==', '!=', '<=', '>=', '<', '>', '&&', '||', 'or', 'and', 'xor', '.'))) {
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

        return $ret;
    }
}