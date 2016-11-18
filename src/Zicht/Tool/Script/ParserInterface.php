<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Script;

/**
 * Interface for parsing a token stream
 */
interface ParserInterface
{
    /**
     * Parses the token stream and returns a node implementation
     *
     * @param TokenStream $stream
     * @return Node\NodeInterface
     */
    public function parse(TokenStream $stream);
}