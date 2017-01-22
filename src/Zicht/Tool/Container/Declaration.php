<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Container;

use Zicht\Tool\Script\Buffer;
use Zicht\Tool\Script\Node\Node;

/**
 * Represents a value declaration in the container context.
 */
class Declaration implements Node
{
    protected $path;
    protected $expr;

    /**
     * Constructor.
     *
     * @param array $path
     * @param null|Node $value
     */
    public function __construct(array $path, Node $value = null)
    {
        $this->path = $path;
        $this->expr = $value;
    }

    /**
     * @{inheritDoc}
     */
    public function compile(Buffer $buffer)
    {
        $buffer
            ->write('$z->decl(')->asPhp($this->path)->raw(', function($z) {')->eol()
            ->indent(1);
        $this->compileBody($buffer);
        $buffer->eol()->indent(-1)->writeln('});');
    }


    /**
     * Compiles the definition body
     *
     * @param \Zicht\Tool\Script\Buffer $buffer
     * @return void
     */
    protected function compileBody(Buffer $buffer)
    {
        $buffer->write('return ');
        $this->expr->compile($buffer);
        $buffer->raw(';');
    }
}
