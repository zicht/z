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

        $env = null;
        if ($input->hasArgument('env') && $input->getArgument('env')) {
            $env = $input->getArgument('env');
        } elseif ($input->hasArgument('target_env') && $input->getArgument('target_env')) {
            $env = $input->hasArgument('target_env');
        }

        if ($env) {
            $this->container->select('env', $env);
            // forward compatibility with 1.1+
            $this->container->set('target_env', $env);
        }
    }
}