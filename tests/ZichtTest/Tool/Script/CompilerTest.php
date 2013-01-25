<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace ZichtTest\Tool\Script;

use Zicht\Tool\Script\Tokenizer;
use Zicht\Tool\Script\TokenStream;
use Zicht\Tool\Script\Parser;

/**
 * @covers Zicht\Tool\Script\Compiler
 */
class CompilerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider cases
     */
    function testCompilation($input, $result)
    {
        $compiler = new \Zicht\Tool\Script\Compiler();
        $this->assertEquals($result, $compiler->compile($input));
    }


    /**
     *
     */
    function cases()
    {
        return array(
            array('', ''),
            array('$(w00t)', '$z->cmd($z->value($z->resolve(\'w00t\')));'),
            array('a $(w00t) b', "\$z->cmd('a ' . \$z->value(\$z->resolve('w00t')) . ' b');"),
            array('a $(w00t()) b', "\$z->cmd('a ' . \$z->value(\$z->call('w00t')) . ' b');"),
//            array('a $(w00t(b)) b', "'a ' . \$z->value(call_user_func(\$z['w00t'], \$z['b'])) . ' b'"),
//            array('a $(w00t(b())) b', "'a ' . \$z->value(call_user_func(\$z['w00t'], call_user_func(\$z['b']))) . ' b'"),
        );
    }
}