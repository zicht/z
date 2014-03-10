<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Script\Node\Script;

use Zicht\Tool\Script\Buffer;

interface Annotation
{
    public function beforeScript(Buffer $buffer);
    public function afterScript(Buffer $buffer);
}