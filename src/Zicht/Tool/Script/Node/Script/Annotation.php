<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Script\Node\Script;

use \Zicht\Tool\Script\Buffer;

/**
 * Script annotation node interface
 */
interface Annotation
{
    /**
     * Allows the annotation to modify the buffer before the script is compiled.
     *
     * @param Buffer $buffer
     * @return void
     */
    public function beforeScript(Buffer $buffer);

    /**
     * Allows the annotation to modify the buffer after the script is compiled.
     *
     * @param Buffer $buffer
     * @return void
     */
    public function afterScript(Buffer $buffer);
}