<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Script;

/**
 * Wrapper class for tokens
 */
final class Token
{
    /**
     * @deprecated
     */
    const LEGACY_ENV = 'env';

    /**
     * Data token type
     */
    const DATA = 'data';

    /**
     * Start of expression token type
     */
    const EXPR_START = 'expr_start';

    /**
     * End of expression token type
     */
    const EXPR_END = 'expr_end';

    /**
     * Identifier token type
     */
    const IDENTIFIER = 'identifier';

    /**
     * Whitespace token type
     */
    const WHITESPACE = 'whitespace';

    /**
     * Number token type
     */
    const NUMBER = 'number';

    /**
     * String token type
     */
    const STRING = 'string';

    /**
     * Operator token type
     */
    const OPERATOR = 'operator';

    /**
     * Value token type
     */
    const KEYWORD = 'keyword';

    /**
     * @var string
     */
    public $type;

    /**
     * @var mixed
     */
    public $value;

    /**
     * Construct the token with the passed type and value
     *
     * @param string $type
     * @param string $value
     */
    public function __construct($type, $value = null)
    {
        $this->type = $type;
        $this->value = $value;
    }


    /**
     * Checks if the token matches the passed type and/or value
     *
     * @param mixed $type
     * @param mixed $value
     * @return bool
     */
    public function match($type, $value = null)
    {
        if ($this->type === $type || (is_array($type) && in_array($this->type, $type))) {
            if (null === $value || $this->value == $value || (is_array($value) && in_array($this->value, $value))) {
                return true;
            }
        }
        return false;
    }
}