<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool\Versioning;
 
class Resolver implements ResolverInterface
{
    function resolve($name)
    {

        trigger_error(E_USER_WARNING, "Version resolution is not implemented yet");
        return 'trunk';
    }
}