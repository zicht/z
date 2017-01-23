<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Tool\Command;

use Symfony\Component\Console\Command\Command;
use Zicht\Tool\Container\Container;
use Zicht\Tool\Application;

/**
 * Base command containing a reference to the container
 */
abstract class BaseCommand extends Command
{
    /**
     * @return Container
     */
    protected function getContainer()
    {
        $app = $this->getApplication();

        if (!($app instanceof Application)) {
            throw new \UnexpectedValueException("This command can only be part of " . Application::class);
        }
        
        return $this->getApplication()->getContainer();
    }
}
