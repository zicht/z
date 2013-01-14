<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Script;

class Parser extends AbstractParser
{
    function __construct(TokenStream $input)
    {
        $this->input = $input;
    }


    function parse()
    {
        $ret = new Node\Script();

        $this->input->next();
        while ($this->input->valid()) {
            $cur = $this->input->current();
            if ($cur->match(Token::EXPR_START)) {
                $this->input->next();
                $parser = new Parser\Expression($this);
                $ret->append($parser->parse($this->input));
                $this->input->expect(Token::EXPR_END);
            } elseif ($cur->match(token::DATA)) {
                $ret->append(new Node\Expr\Data($cur->value));
                $this->input->next();
            }
        }

        return $ret;
    }
}