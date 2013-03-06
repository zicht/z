<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */
namespace Zicht\Tool\Container;

use Zicht\Tool\Script\Node\Definition;

class ContainerBuilder
{
    function __construct($config)
    {
        $this->config = $config;

        $this->exprcompiler  = $this->createExpressionCompiler();
        $this->scriptcompiler = $this->createScriptCompiler();
    }



    public function build()
    {
        $this->node = new ContainerNode();

        $traverser = $this->createNodeCreatorTraverser($this->config);
        $result = $traverser->traverse();
        $gatherer = $this->createNodeGathererTraverser($result);
        $gatherer->traverse();

        return $this->node;
    }


    public function createNodeGathererTraverser($result)
    {
        $gatherer = new Traverser($result);
        $gatherer->addVisitor(
            array($this, 'addNode'),
            function ($path, $node) {
                return $node instanceof \Zicht\Tool\Script\Node\Node;
            },
            Traverser::AFTER
        );
        return $gatherer;
    }


    function createTaskNode($path, $node)
    {
        return new Task($path, $node);
    }


    function createDefinitionNode($path, $node)
    {
        return new Definition($path, $node);
    }


    function createScriptCompiler()
    {
        return new \Zicht\Tool\Script\Compiler(new \Zicht\Tool\Script\Parser());
    }


    function createExpressionCompiler()
    {
        return new \Zicht\Tool\Script\Compiler(new \Zicht\Tool\Script\Parser\Expression(), new \Zicht\Tool\Script\Tokenizer\Expression());
    }



    function createSetNode($path, $node) {
        $v = trim($node);
        if (substr($v, 0, 1) == '?') {
            $conditional = true;
            $v = ltrim(substr($v, 1));
        } else {
            $conditional = false;
        }
        return new \Zicht\Tool\Script\Node\Task\SetNode(end($path), $this->exprcompiler->parse($v), $conditional);
    }


    function createExpressionNode($path, $node)
    {
        return $this->exprcompiler->parse($node);
    }


    function createScriptNode($path, $node)
    {
        return $this->scriptcompiler->parse(trim($node));
    }


    function addNode($path, $node)
    {
        $this->node->append($node);
        return $node;
    }


    function createNodeCreatorTraverser($config) {
        $traverser = new \Zicht\Tool\Container\Traverser($config);

        $traverser->addVisitor(
            array($this, 'createSetNode'),
            function($path) {
                return (count($path) == 4 && $path[0] == 'tasks' && $path[2] == 'set');
            },
            Traverser::BEFORE
        );

        $traverser->addVisitor(
            array($this, 'createExpressionNode'),
            function($path) {
                return count($path) === 3 && $path[0] == 'tasks' && in_array($path[2], array('unless', 'assert', 'yield'));
            },
            Traverser::BEFORE
        );
        $traverser->addVisitor(
            array($this, 'createScriptNode'),
            function($path) {
                return count($path) == 4 && $path[0] == 'tasks' && in_array($path[2], array('do', 'pre' ,'post'));
            },
            Traverser::BEFORE
        );
        $traverser->addVisitor(
            array($this, 'createTaskNode'),
            function($path) {
                return count($path) == 2 && $path[0] == 'tasks';
            },
            Traverser::AFTER
        );
        $traverser->addVisitor(
            array($this, 'createDefinitionNode'),
            function($path, $node) {
                return $path[0] !== 'tasks' && is_scalar($node);
            },
            Traverser::AFTER
        );

        return $traverser;
    }
}