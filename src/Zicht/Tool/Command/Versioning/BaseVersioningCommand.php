<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Tool\Command\Versioning;

use Zicht\Tool\Command\BaseCommand;

class BaseVersioningCommand extends BaseCommand
{
    /**
     * @return \Zicht\Tool\Versioning\VersioningInterface
     */
    function getVersioning()
    {
        return $this->container->get('versioning');
    }
}