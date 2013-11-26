<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Tool\Command;

use \Symfony\Component\Console\Command\Command;
use \Symfony\Component\Console\Input\InputOption;

use \Zicht\Tool\Container\Container;

/**
 * Base command containing a reference to the container
 */
class BaseCommand extends Command
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * Set the container instance
     *
     * @param \Zicht\Tool\Container\Container $container
     * @return void
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }
}