<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Tool\Command;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BaseCommand extends \Symfony\Component\Console\Command\Command
{
    function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct();
    }


    protected function initialize(InputInterface $input, OutputInterface $output) {
        parent::initialize($input, $output);

        $env = null;
        try {
            $env = $input->getArgument('environment');
        } catch(\Exception $e) {
        }
        if (null !== $env) {
            $this->container->get('task_context')->setEnvironment($env);
        }
    }
}