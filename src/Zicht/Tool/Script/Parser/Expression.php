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
use Zicht\Tool\Script\AbstractParser;

class Expression extends AbstractParser
{
    function parse(\Zicht\Tool\Script\TokenStream $stream)
    {
        if ($stream->match(Token::IDENTIFIER)) {
            $name = $stream->current()->value;
            $stream->next();

            if ($stream->match('(')) {
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
            $this->err($stream);
        }
        return new \Zicht\Tool\Script\Node\Expr\Expr($ret);
    }
}