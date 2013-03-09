<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */
namespace Zicht\Tool\Container\Context;

use \Symfony\Component\PropertyAccess\PropertyPathInterface;
use \Symfony\Component\PropertyAccess\Exception\OutOfBoundsException;


/**
 * Property path implementation for the container context.
 */
class ArrayPropertyPath implements \IteratorAggregate, PropertyPathInterface
{
    /**
     * Constructor.
     *
     * @param array $elements
     */
    public function __construct(array $elements)
    {
        $this->elements = $elements;
    }

    /**
     * @{inheritDoc}
     */
    public function getLength()
    {
        return count($this->elements);
    }

    /**
     * @{inheritDoc}
     */
    public function getParent()
    {
        return new ArrayPropertyPath(array_slice($this->elements, -1));
    }

    /**
     * @{inheritDoc}
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * @{inheritDoc}
     */
    public function getElement($index)
    {
        $this->assertValidIndex($index);
        return $this->elements[$index];
    }

    /**
     * @{inheritDoc}
     */
    public function isProperty($index)
    {
        $this->assertValidIndex($index);
        return false;
    }

    /**
     * @{inheritDoc}
     */
    public function isIndex($index)
    {
        $this->assertValidIndex($index);
        return true;
    }


    /**
     * @{inheritDoc}
     */
    public function __toString()
    {
        return join('.', $this->elements);
    }

    /**
     * @{inheritDoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->elements);
    }


    /**
     * Utility to throw an exception if the index is not valid.
     *
     * @param mixed $index
     * @return void
     */
    private function assertValidIndex($index)
    {
        if (!isset($this->elements[$index])) {
            throw new OutOfBoundsException("{$index} not found");
        }
    }
}