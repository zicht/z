<?php

namespace Zicht\Tool;

class Parser
{
    public function __construct()
    {
    }


    public function parse($str)
    {
        $stack = [$this->newNode('root', -1)];

        $offset = 0;
        foreach (explode("\n", $str) as $line) {
            preg_match('/^( *)(.*)/s', $line, $indentMatch);
            $lineIndent = strlen($indentMatch[1]);
            $lineValue = $indentMatch[2];

            $lineValue = preg_replace('/#.*/', '', $lineValue);
            $previousNode = array_pop($stack);

            if (preg_match('/(^\w+(?:\.\w+)*):(\s*)(.*)/', $lineValue, $nodeMatch)) {
                $node = $this->newNode($nodeMatch[1], $lineIndent, strlen($nodeMatch[2]), $nodeMatch[3]);

                while ($previousNode['indent'] >= $node['indent']) {
                    $parent = array_pop($stack);
                    $parent['children'][]= $previousNode;
                    $previousNode = $parent;
                }

                array_push($stack, $previousNode);
                $previousNode = $node;
            } elseif (preg_match('/^-\s*(.*)/', $lineValue, $lineMatch)) {
                $previousNode['children'][]= ['data' => $lineMatch[1]];
            } elseif (strlen(trim($lineValue))) {
                if ($lineIndent < $previousNode['data_indent']) {
                    $this->err("Unexpected decreasing indent", 'STDIN', $str, $offset + $lineIndent);
                } else {
                    if ($previousNode['data']) {
                        $previousNode['data'] .= "\n" . substr($line, $previousNode['data_indent']);
                    } else {
                        $previousNode['data_indent']= $lineIndent;
                        $previousNode['data']= $lineValue;
                    }
                }
            }
            array_push($stack, $previousNode);

            $offset += strlen($lineValue) +1;
        }


        while(count($stack) >= 2) {
            $child = array_pop($stack);
            $parent = array_pop($stack);
            $parent['children'][]= $child;
            array_push($stack, $parent);
        }

        $root = array_pop($stack);

        return $this->fold($root);
    }

    private function fold($node)
    {
        if ($node['data']) {
            return $node['data'];
        } elseif ($node['children']) {
            $ret = [];
            foreach ($node['children'] as $child) {
                if (!isset($child['name'])) {
                    $ret[]= $this->fold($child);
                } else {
                    $ret[$child['name']]= $this->fold($child);
                }
            }
            return $ret;
        } else {
            return null;
        }
    }


    public function formatPosition($offset, $str)
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


    public function err($message, $file, $str, $offset)
    {
        $lineNr = substr_count(substr($str, 0, $offset), "\n");
        $msg = sprintf("Parse error in %s at line %d:\n", $file, $lineNr +1);
        $msg .= sprintf($this->formatPosition($offset, $str));
        $msg .= sprintf("%s\n", $message);

        throw new \UnexpectedValueException($msg);
    }

    private function newNode($name, $indent, $dataIndent = 0, $data = null)
    {
        $data = ltrim($data);
        $ret = [
            'name' => $name,
            'indent' => $indent,
            'data_indent' => $data ? ($indent + strlen($name) + 1 + $dataIndent) : null, // the 1 is the colon
            'data' => $data,
            'children' => []
        ];
        return $ret;
    }
}

