<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Tool\Container;

/**
 * Thrown whenever a circular reference is detected.
 */
class CircularReferenceException extends \LogicException
{
}