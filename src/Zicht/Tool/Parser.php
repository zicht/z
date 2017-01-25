<?php

namespace Zicht\Tool;

use Zicht\Tool\Script\Node\Expr\ListNode;
use Zicht\Tool\Script\Node\Expr\Literal;
use Zicht\Tool\Script\Node\Expr\Str;
use Zicht\Tool\Script\Node\NodeInterface;
use Zicht\Tool\Script\Parser\Expression as ExpressionParser;
use Zicht\Tool\Script\Tokenizer\Expression as ExpressionTokenizer;
use Zicht\Tool\Script\TokenStream;

/**
 * This parser parses a yaml-like structure into a context-free tree of nodes.
 */
class Parser
{

    public function __construct($file, $str)
    {
        $this->expressionParser = new ExpressionParser();

        $this->file = $file;
        $this->str = $str;
    }


    public function parse()
    {
        $root = new ListNode();

        $root->attributes['indent'] = -1;

        $stack = [$root];

        $offset = 0;

        foreach (explode("\n", $this->str) as $line) {
            preg_match('/^( *)(.*)/s', $line, $indentMatch);
            $lineIndent = strlen($indentMatch[1]);
            $lineValue = $indentMatch[2];

            $lineValue = preg_replace('/#.*/', '', $lineValue);
            $previousNode = array_pop($stack);

            if (preg_match('/(?:-|(?P<name>^\w+(?:\.\w+)*(?:\[\])?):)(?P<data_indent>\s*)(?P<data>.*)/', $lineValue, $nodeMatch)) {
                if (isset($nodeMatch['data'])) {
                    if ($nodeMatch['data'] === '|') {
                        $node = new Str('');
                    } else {
                        $node = new Str($nodeMatch['data']);
                        $node->attributes['data_indent'] = $lineIndent + strlen($nodeMatch['name']) + 1 + strlen($nodeMatch['data_indent']);
                    }
                } else {
                    $node = new ListNode();
                }
                $node->attributes['indent'] = $lineIndent;
                if (!empty($nodeMatch['name'])) {
                    $node->attributes['name'] = $nodeMatch['name'];
                }
                $node->attributes['offset']= $offset + $lineIndent;

                while ($previousNode->attributes['indent'] >= $node->attributes['indent']) {
                    $parent = array_pop($stack);
                    $parent = $this->appendTo($parent, $previousNode);
                    $previousNode = $parent;
                }

                array_push($stack, $previousNode);

                $previousNode = $node;
            } elseif (strlen(trim($lineValue))) {
                if (isset($previousNode->attributes['data_indent']) && $lineIndent < $previousNode->attributes['data_indent']) {
                    $this->err("Unexpected decreasing indent. Expected indent is {$previousNode->attributes['data_indent']}, found {$lineIndent}", $offset + $lineIndent);
                } else {
                    if ($previousNode->value) {
                        $previousNode->value .= "\n" . substr($line, $previousNode->attributes['data_indent']);
                    } else {
                        $previousNode->value = $lineValue;
                        $previousNode->attributes['data_indent']= $lineIndent;
                    }
                }
            }
            array_push($stack, $previousNode);

            $offset += strlen($line) +1;
        }


        while(count($stack) >= 2) {
            $child = array_pop($stack);
            $parent = array_pop($stack);
            $parent = $this->appendTo($parent, $child);
            array_push($stack, $parent);
        }

        $root = array_pop($stack);

        if (!$root->nodes) {
            return new Literal(null);
        }

        return $root;
    }


    public static function formatPosition($offset, $str)
    {
        $lineNr = substr_count(substr($str, 0, $offset), "\n");
        $lines = explode("\n", $str);

        $tmp = $offset;
        $lineOffset = 0;

        while ($tmp > 0 && $str[$tmp-1] !== "\n") {
            $tmp --;
            $lineOffset ++;
        }

        return sprintf("\n%4d. %s\n      %s^-- here\n", $lineNr +1, $lines[$lineNr], str_repeat(" ", $lineOffset));
    }


    public function err($message, $offset)
    {
        $lineNr = substr_count(substr($this->str, 0, $offset), "\n");
        $msg = sprintf("Parse error in %s at line %d:\n", $this->file, $lineNr +1);
        $msg .= sprintf(self::formatPosition($offset, $this->str));
        $msg .= sprintf("%s\n", $message);

        throw new \UnexpectedValueException($msg);
    }


    private function appendTo(NodeInterface $parent, NodeInterface $node)
    {
        if ($parent instanceof Str) {
            $newParent = new ListNode();
            $newParent->attributes = $parent->attributes;
            $parent = $newParent;
        }

        if ($node instanceof Str && preg_match('/^(\{|\[).*(\}|\])$/s', trim($node->value))) {
            $value = $this->expressionParser->parse(new TokenStream((new ExpressionTokenizer())->getTokens($node->value)));
            $value->attributes = $node->attributes;
            $parent->append($value);
        } else {
            $parent->append($node);
        }

        return $parent;
    }
}

