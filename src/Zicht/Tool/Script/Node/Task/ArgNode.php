<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Script\Node\Task;

use \Zicht\Tool\Script\Buffer;
use \Zicht\Tool\Script\Node\Branch;
use \Zicht\Tool\Util;

/**
 * A node for the "args" section of a task
 */
class ArgNode extends Branch
{
    /**
     * Constructor.
     *
     * @param string $name
     * @param \Zicht\Tool\Script\Node\Node $expr
     * @param bool $conditional
     */
    public function __construct($name, $expr, $conditional)
    {
        parent::__construct();
        $this->nodes[0]= $expr;
        $this->name = $name;
        $this->conditional = $conditional;
    }


    /**
     * Compiles the arg node.
     *
     * @param \Zicht\Tool\Script\Buffer $buffer
     * @return void
     */
    public function compile(Buffer $buffer)
    {
        $name = explode('.', $this->name);
        $phpName = Util::toPhp($name);

        if ($this->conditional) {
            $buffer->writeln(sprintf('if ($z->isEmpty(%s)) {', $phpName))->indent(1);
            if (!$this->nodes[0]) {
                $buffer->writeln(
                    sprintf(
                        'throw new \RuntimeException(\'required variable %s is not defined\');',
                        join('.', $name)
                    )
                );
            }
        }
        if ($this->nodes[0]) {
            $buffer->write('$z->set(')->raw($phpName)->raw(', ');
            $this->nodes[0]->compile($buffer);
            $buffer->raw(');')->eol();
        }
        if ($this->conditional) {
            $buffer->indent(-1)->writeln('}');
        }
    }
}