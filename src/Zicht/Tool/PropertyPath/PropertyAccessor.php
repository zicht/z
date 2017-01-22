<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */
namespace Zicht\Tool\PropertyPath;

/**
 * Utility functions to access an arbitrary property path within a tree-like structure.
 */
class PropertyAccessor
{
    /**
     * Get a property value by a path of property names or key names.
     *
     * @param mixed $subject
     * @param array $path
     * @param bool $notFoundIsError
     * @return null
     *
     * @throws \InvalidArgumentException
     */
    public static function getByPath($subject, array $path, $notFoundIsError = true)
    {
        $ptr = & $subject;
        foreach ($path as $key) {
            if (is_object($ptr) && property_exists($ptr, $key)) {
                $ptr = & $ptr->$key;
            } elseif (is_array($ptr) && array_key_exists($key, $ptr)) {
                $ptr = & $ptr[$key];
            } else {
                if ($notFoundIsError === true) {
                    throw new \OutOfBoundsException("Path not found: " . implode('.', $path) . ", key {$key} did not resolve");
                }
                return null;
            }
        }
        return $ptr;
    }


    /**
     * Set a property value by a path of property or key names.
     *
     * @param mixed &$subject
     * @param array $path
     * @param mixed $value
     * @return mixed
     */
    public static function setByPath(&$subject, array $path, $value)
    {
        $ptr = & $subject;
        foreach ($path as $key) {
            if (is_object($ptr)) {
                if (!isset($ptr->$key)) {
                    $ptr->$key = array();
                }
                $ptr = & $ptr->$key;
            } elseif (is_array($ptr)) {
                if (!isset($ptr[$key])) {
                    $ptr[$key] = array();
                }
                $ptr = & $ptr[$key];
            }
        }
        $ptr = $value;
        return $subject;
    }
}