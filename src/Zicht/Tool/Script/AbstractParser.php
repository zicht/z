<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Script;

/**
 * Base class for parsing.
 */
abstract class AbstractParser implements ParserInterface
{
    /**
     * Constructs the parser with an optional parent parser
     *
     * @param ParserInterface $parent
     */
    public function __construct(ParserInterface $parent = null)
    {
        $this->parent = $parent;
    }

    /**
     * Triggers a parse error
     *
     * @param TokenStream $stream
     * @return void
     */
    final public function err(TokenStream $stream)
    {
        throw new \UnexpectedValueException(
            "Unexpected input near {$stream->current()->value} at offset {$stream->key()}"
        );
    }
}