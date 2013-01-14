<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace ZichtTest\Tool\Script;

use Zicht\Tool\Script\Tokenizer;
use Zicht\Tool\Script\Token;

class TokenizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider data
     */
    function testTokenization($input, $tokens)
    {
        $tokenizer = new Tokenizer($input);
        $this->assertEquals($tokens, $tokenizer->getTokens());
    }

    /**
     *
     */
    function data()
    {
        return array(
            array('abc', array(new Token(Token::DATA, 'abc'))),
            array(
                'abc $(abc)',
                array(
                    new Token(Token::DATA, 'abc '),
                    new Token(Token::EXPR_START, '$('),
                    new Token(Token::IDENTIFIER, 'abc'),
                    new Token(Token::EXPR_END, ')'),
                )
            ),
            array(
                'abc $(?abc)',
                array(
                    new Token(Token::DATA, 'abc '),
                    new Token(Token::EXPR_START, '$('),
                    new Token('?'),
                    new Token(Token::IDENTIFIER, 'abc'),
                    new Token(Token::EXPR_END, ')'),
                )
            ),
            array(
                'abc $(?abc abc)',
                array(
                    new Token(Token::DATA, 'abc '),
                    new Token(Token::EXPR_START, '$('),
                    new Token('?'),
                    new Token(Token::IDENTIFIER, 'abc'),
                    new Token(Token::WHITESPACE, ' '),
                    new Token(Token::IDENTIFIER, 'abc'),
                    new Token(Token::EXPR_END, ')'),
                )
            ),
            array(
                'abc $(abc())',
                array(
                    new Token(Token::DATA, 'abc '),
                    new Token(Token::EXPR_START, '$('),
                    new Token(Token::IDENTIFIER, 'abc'),
                    new Token('('),
                    new Token(')'),
                    new Token(Token::EXPR_END, ')'),
                )
            ),
            array(
                'abc $(abc(abc()))',
                array(
                    new Token(Token::DATA, 'abc '),
                    new Token(Token::EXPR_START, '$('),
                    new Token(Token::IDENTIFIER, 'abc'),
                    new Token('('),
                    new Token(Token::IDENTIFIER, 'abc'),
                    new Token('('),
                    new Token(')'),
                    new Token(')'),
                    new Token(Token::EXPR_END, ')'),
                )
            ),
            array(
                'abc $(abc(abc())) abc',
                array(
                    new Token(Token::DATA, 'abc '),
                    new Token(Token::EXPR_START, '$('),
                    new Token(Token::IDENTIFIER, 'abc'),
                    new Token('('),
                    new Token(Token::IDENTIFIER, 'abc'),
                    new Token('('),
                    new Token(')'),
                    new Token(')'),
                    new Token(Token::EXPR_END, ')'),
                    new Token(Token::DATA, ' abc'),
                )
            ),
        );
    }
}

