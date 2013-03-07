<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */
namespace Zicht\Tool\Container;

use \Zicht\Tool\Script\Node\Definition;
use \Zicht\Tool\Script\Node\Node;
use \Zicht\Tool\Script\Compiler;
use \Zicht\Tool\Script\Parser;
use \Zicht\Tool\Script\Parser\Expression as ExpressionParser;
use \Zicht\Tool\Script\Tokenizer\Expression as ExpressionTokenizer;
use \Zicht\Tool\Script\Node\Task\SetNode;

/**
 * The container builder converts a config tree into a compilable ContainerNode
 */
class ContainerBuilder
{
    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct($config)
    {
        $this->config = $config;

        $this->exprcompiler  = $this->createExpressionCompiler();
        $this->scriptcompiler = $this->createScriptCompiler();
    }

    /**
     * Build the container node
     *
     * @return ContainerNode
     */
    public function build()
    {
        $traverser = $this->createNodeCreatorTraverser($this->config);
        $result = $traverser->traverse();

        $node = new ContainerNode();
        $gatherer = $this->createNodeGathererTraverser($result, $node);
        $gatherer->traverse();

        return $node;
    }


    /**
     * Creates the traverser that gathers all nodes that are specified in the tree.
     *
     * @param array $result
     * @param \Zicht\Tool\Script\Node\Branch $containerNode
     * @return Traverser
     */
    public function createNodeGathererTraverser($result, $containerNode)
    {
        $gatherer = new Traverser($result);
        $gatherer->addVisitor(
            function($path, $node) use($containerNode) {
                $containerNode->append($node);
            },
            function ($path, $node) {
                return $node instanceof Node;
            },
            Traverser::AFTER
        );
        return $gatherer;
    }


    /**
     * Creates a task node for the specified path
     *
     * @param array $path
     * @param array $node
     * @return Task
     */
    public function createTaskNode($path, $node)
    {
        return new Task($path, $node);
    }


    /**
     * Creates a definition node for the specified path
     *
     * @param array $path
     * @param array $node
     * @return \Zicht\Tool\Script\Node\Definition
     */
    public function createDefinitionNode($path, $node)
    {
        return new Definition($path, $node);
    }


    /**
     * Creates the script compiler.
     *
     * @return \Zicht\Tool\Script\Compiler
     */
    public function createScriptCompiler()
    {
        return new Compiler(new Parser());
    }


    /**
     * Creates the expression compiler
     *
     * @return \Zicht\Tool\Script\Compiler
     */
    public function createExpressionCompiler()
    {
        return new Compiler(new ExpressionParser(), new ExpressionTokenizer());
    }


    /**
     * Creates a node for the 'set' definition of the task.
     *
     * @param array $path
     * @param array $node
     * @return \Zicht\Tool\Script\Node\Task\SetNode
     */
    public function createSetNode($path, $node)
    {
        $v = trim($node);
        if (substr($v, 0, 1) == '?') {
            $conditional = true;
            $v = ltrim(substr($v, 1));
        } else {
            $conditional = false;
        }
        return new SetNode(end($path), $this->exprcompiler->parse($v), $conditional);
    }


    /**
     * Creates an expression node at the specified path.
     *
     * @param array $path
     * @param array $node
     * @return \Zicht\Tool\Script\Node\Node
     */
    public function createExpressionNode($path, $node)
    {
        return $this->exprcompiler->parse($node);
    }


    /**
     * Creates a script node at the specified path.
     *
     * @param array $path
     * @param array $node
     * @return \Zicht\Tool\Script\Node\Node
     */
    public function createScriptNode($path, $node)
    {
        return $this->scriptcompiler->parse(trim($node));
    }


    /**
     * Creates the traverser that creates relevant nodes at all known paths.
     *
     * @param array $config
     * @return Traverser
     */
    public function createNodeCreatorTraverser($config)
    {
        $traverser = new Traverser($config);

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
                return
                    count($path) === 3
                    && $path[0] == 'tasks'
                    && in_array($path[2], array('unless', 'assert', 'yield'))
                ;
            },
            Traverser::BEFORE
        );
        $traverser->addVisitor(
            array($this, 'createScriptNode'),
            function($path) {
                return
                    count($path) == 4
                    && $path[0] == 'tasks'
                    && in_array($path[2], array('do', 'pre' ,'post'))
                ;
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