<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Script;

/**
 * Interface for tokenizers.
 */
interface TokenizerInterface
{
    /**
     * Returns the tokens in the string, and updated the needle to the specified index.
     *
     * @param string $string
     * @param int &$needle
     * @return array
     */
    public function getTokens($string, &$needle = 0);
}