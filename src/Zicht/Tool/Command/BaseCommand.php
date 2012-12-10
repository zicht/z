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
        parent::__construct();
        $this->container = $container;
    }


    protected function initialize(InputInterface $input, OutputInterface $output) {
        parent::initialize($input, $output);

        if ($value = $input->getArgument('environment')) {
            $this->container->get('task_context')->setEnvironment($value);
        }
    }
}