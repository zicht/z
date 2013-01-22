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
     * Set the container instance
     *
     * @param \Zicht\Tool\Container\Container $container
     * @return void
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }


    /**
     * Initializes the environment in the container if set as an input option
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return void
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        if ($input->hasArgument('env') && $input->getArgument('env')) {
            $this->container->select('env', $input->getArgument('env'));
        }
    }
}