<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Versioning;
 
final class Version
{
    const BRANCH    = 'branch';
    const TAG       = 'tag';

    private $type;
    private $name;

    function __construct($type, $name, $peg = null) {
        $this->type = $type;
        $this->name = $name;
        $this->peg = $peg;
    }


    function getPeg() {
        return $this->peg;
    }


    function getType() {
        return $this->type;
    }


    function getName() {
        return $this->name;
    }
}