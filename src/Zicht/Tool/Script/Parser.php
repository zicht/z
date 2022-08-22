<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Tool\Script;

use Zicht\Tool\Script\Exception\TokenException;

/**
 * Parser for root nodes of the script
 */
class Parser extends AbstractParser
{
    /**
     * Parses the input tokenstream and returns a Script node
     *
     * @param TokenStream $input
     * @return Node\Script
     * @throws TokenException
     */
    public function parse(TokenStream $input)
    {
        $exprParser = new Parser\Expression($this);
        $ret = new Node\Script();
        if ($input->valid()) {
            do {
                $hasMatch = false;
                if ($input->match(Token::EXPR_START, '@(')) {
                    $input->next();
                    $type = $input->expect(Token::IDENTIFIER)->value;

                    switch ($type) {
                        case 'sh':
                            $ret->append(new Node\Script\Shell($exprParser->parse($input)));
                            break;
                        case 'for':
                            $key = null;
                            $name = $input->expect(Token::IDENTIFIER)->value;
                            if ($input->match(',')) {
                                $input->next();
                                $key = $name;
                                $name = $input->expect(Token::IDENTIFIER)->value;
                            }
                            $input->expect(Token::KEYWORD, 'in');
                            $ret->append(new Node\Script\ForIn($exprParser->parse($input), $key, $name));
                            break;
                        case 'each':
                            $ret->append(new Node\Script\ForIn($exprParser->parse($input), null, null));
                            break;
                        case 'with':
                            $expr = $exprParser->parse($input);
                            $input->expect(Token::KEYWORD, 'as');
                            $name = $input->expect(Token::IDENTIFIER)->value;
                            $ret->append(new Node\Script\With($expr, $name));
                            break;
                        case 'if':
                            $ret->append(new Node\Script\Conditional($exprParser->parse($input)));
                            break;
                        default:
                            throw new TokenException("Unknown EXPR_START token at this point: {$type}");
                    }
                    $input->expect(Token::EXPR_END);
                    $hasMatch = true;
                }
                while ($input->match(Token::DATA) && preg_match('/^\s+$/', $input->current()->value)) {
                    $input->next();
                }
            } while ($hasMatch && $input->valid());
        }
        while ($input->valid()) {
            $cur = $input->current();
            if ($cur->match(Token::EXPR_START, '$(')) {
                $input->next();
                $ret->append(new Node\Expr\Expr($exprParser->parse($input)));
                $input->expect(Token::EXPR_END);
            } elseif ($cur->match(Token::DATA)) {
                $ret->append(new Node\Expr\Data($cur->value));
                $input->next();
            } else {
                throw new TokenException("Unxpected token: " . $input->current()->type);
            }
        }

        return $ret;
    }
}
