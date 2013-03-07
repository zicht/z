<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Tool\Command;

use \Symfony\Component\DependencyInjection\ContainerInterface;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\Console\Command\Command;
use \Symfony\Component\Console\Input\InputOption;

use \Zicht\Tool\Container\Container;

/**
 * Base command containing a reference to the container
 */
class BaseCommand extends Command
{
    protected $container;

    /**
     * Constructor.
     *
     * @param \Zicht\Tool\Container\Container $container
     * @param string $name
     */
    public function __construct(Container $container, $name = null)
    {
        parent::__construct($name);
        $this->setContainer($container);
    }


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