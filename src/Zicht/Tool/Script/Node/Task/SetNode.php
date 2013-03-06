<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Script\Node\Task;

use Zicht\Tool\Script\Buffer;

class SetNode extends \Zicht\Tool\Script\Node\Branch
{
    function __construct($name, $expr, $conditional)
    {
        parent::__construct();
        $this->nodes[0]= $expr;
        $this->name = $name;
        $this->conditional = $conditional;
    }


    function compile(Buffer $buffer) {
        $name = explode('.', $this->name);
        $phpName = \Zicht\Tool\Util::toPhp($name);

        if ($this->conditional) {
            $buffer->writeln(sprintf('if (!$z->has(%s)) {', $phpName))->indent(1);
            if (!$this->nodes[0]) {
                $buffer->writeln(sprintf(
                    'throw new \RuntimeException(\'required variable %s is not defined\');',
                    join('.', $name)
                ));
            }
        }
        if ($this->nodes[0]) {
            $buffer->write('$z->set(')->raw($phpName)->raw(', ');
            $this->nodes[0]->compile($buffer);
            $buffer->raw(');')->eol();
        }
        if($this->conditional) {
            $buffer->indent(-1)->writeln('}');
        }
    }
}