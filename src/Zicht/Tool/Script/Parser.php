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
        $ret = new Node\Script();
        $exprParser = new Parser\Expression($this);

        if ($input->valid() && $input->match(Token::EXPR_START, '?(')) {
            $input->next();
            $ret->append(new \Zicht\Tool\Script\Node\Expr\Conditional($exprParser->parse($input)));
            $input->expect(Token::EXPR_END);
        }
        while ($input->valid()) {
            $cur = $input->current();
            if ($cur->match(Token::EXPR_START, '$(')) {
                $input->next();
                $ret->append(new \Zicht\Tool\Script\Node\Expr\Expr($exprParser->parse($input)));
                $input->expect(Token::EXPR_END);
            } elseif ($cur->match(token::DATA)) {
                $ret->append(new Node\Expr\Data($cur->value));
                $input->next();
            }
        }

        return $ret;
    }
}