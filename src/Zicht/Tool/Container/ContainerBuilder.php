<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */
namespace Zicht\Tool\Container;

use \Zicht\Tool\Script\Node\Node;
use \Zicht\Tool\Script\Node\Task\OptNode;
use \Zicht\Tool\Script\Compiler;
use \Zicht\Tool\Script\Parser;
use \Zicht\Tool\Debug;
use \Zicht\Tool\Script\Parser\Expression as ExpressionParser;
use \Zicht\Tool\Script\Tokenizer\Expression as ExpressionTokenizer;
use \Zicht\Tool\Script\Node\Task\ArgNode;

/**
 * The container builder converts a config tree into a compilable ContainerNode
 */
class ContainerBuilder
{
    protected $expressionPaths = array();
    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct($config)
    {
        $this->config = $config;

        $this->exprcompiler  = new Compiler(new ExpressionParser(), new ExpressionTokenizer());
        $this->scriptcompiler = new Compiler();
    }


    /**
     * Adds a callable to decide if a path should be an expression node.
     *
     * @param callable $callable
     * @return void
     */
    public function addExpressionPath($callable)
    {
        $this->expressionPaths[]= $callable;
    }


    /**
     * Decides if a node should be an expression.
     *
     * @param array $path
     * @param mixed $node
     * @return bool
     */
    public function isExpressionPath($path, $node)
    {
        if (is_scalar($node)) {
            foreach ($this->expressionPaths as $callable) {
                if (call_user_func($callable, $path)) {
                    return true;
                }
            }
        }
        return false;
    }


    /**
     * Build the container node
     *
     * @return ContainerNode
     */
    public function build()
    {
        Debug::enterScope('build');
        $traverser = $this->createNodeCreatorTraverser($this->config);
        $result = $traverser->traverse();

        $node = new ContainerNode();
        $gatherer = $this->createNodeGathererTraverser($result, $node);
        $gatherer->traverse();
        Debug::exitScope('build');

        return $node;
    }


    /**
     * Creates the traverser that gathers all nodes (i.e. Node instances) that are specified in the tree.
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
     * @return Definition
     */
    public function createDefinitionNode($path, $node)
    {
        return new Definition($path, $node);
    }


    /**
     * Creates a definition node for the specified path
     *
     * @param array $path
     * @param array $node
     * @return Declaration
     */
    public function createDeclarationNode($path, $node)
    {
        return new Declaration($path, $this->exprcompiler->parse($node));
    }


    /**
     * Creates a node for the 'args' definition of the task.
     *
     * @param array $path
     * @param string $node
     * @return \Zicht\Tool\Script\Node\Task\ArgNode
     */
    public function createArgNode($path, $node)
    {
        $v = trim($node);
        if (substr($v, 0, 1) == '?') {
            $conditional = true;
            $v = ltrim(substr($v, 1));
        } else {
            $conditional = false;
        }
        return new ArgNode(end($path), $this->exprcompiler->parse($v), $conditional);
    }

    /**
     * Creates a node for the 'opts' definition of the task.
     *
     * @param array $path
     * @param string $node
     * @return \Zicht\Tool\Script\Node\Task\OptNode
     */
    public function createOptNode($path, $node)
    {
        return new OptNode(end($path), $this->exprcompiler->parse($node));
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
            array($this, 'createArgNode'),
            function($path) {
                return (count($path) == 4 && $path[0] == 'tasks' && $path[2] == 'args');
            },
            Traverser::BEFORE
        );
        $traverser->addVisitor(
            array($this, 'createOptNode'),
            function($path) {
                return (count($path) == 4 && $path[0] == 'tasks' && $path[2] == 'opts');
            },
            Traverser::BEFORE
        );

        $traverser->addVisitor(
            array($this, 'createExpressionNode'),
            function($path) {
                return
                    count($path) === 3
                    && $path[0] == 'tasks'
                    && in_array($path[2], array('unless', 'assert', 'yield', 'if'))
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
            array($this, 'createDeclarationNode'),
            array($this, 'isExpressionPath'),
            Traverser::AFTER
        );
        $traverser->addVisitor(
            array($this, 'createDefinitionNode'),
            function($path, $node) {
                return $path[0] !== 'tasks' && (is_scalar($node) || (is_array($node) && count($node) === 0));
            },
            Traverser::AFTER
        );

        return $traverser;
    }
}