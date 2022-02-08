<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
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