<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
 
class Iterator implements IteratorAggregate
{
    function __construct($versionSource) {

    }


    public function getIterator() {
        $iterator = new AppendIterator();
        $iterator->append(new BranchIterator($this->versionSource));
        $iterator->append(new TagIterator($this->versionSource));
        return $iterator;
    }
}