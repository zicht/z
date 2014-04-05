<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Script;


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
     */
    public function parse(TokenStream $input)
    {
        $exprParser = new Parser\Expression($this);
        $ret = new Node\Script();
        if ($input->valid()) {
            do {
                $hasMatch = false;
                if (
                    $input->match(Token::EXPR_START, '?(')
                 || $input->match(Token::EXPR_START, '@(')
                ) {
                    $type = $input->current()->value;
                    $input->next();

                    switch ($type) {
                        case '@(':
                            $ret->append(new Node\Script\Decorator($exprParser->parse($input)));
                            break;
                        case '?(':
                            $ret->append(new Node\Script\Conditional($exprParser->parse($input)));
                            break;
                        default:
                            throw new \UnexpectedValueException("Unknown EXPR_START token at this point: {$type}");
                    }
                    $input->expect(Token::EXPR_END);
                    $hasMatch = true;
                }
            } while($hasMatch && $input->valid());
        }
        while ($input->valid()) {
            $cur = $input->current();
            if ($cur->match(Token::EXPR_START, '$(')) {
                $input->next();
                $ret->append(new Node\Expr\Expr($exprParser->parse($input)));
                $input->expect(Token::EXPR_END);
            } elseif ($cur->match(token::DATA)) {
                $ret->append(new Node\Expr\Data($cur->value));
                $input->next();
            }
        }

        return $ret;
    }
}