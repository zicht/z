<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Tool;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Application extends BaseApplication
{
    function __construct(ContainerInterface $container) {
        parent::__construct('z - The Zicht Tool', \Zicht\Tool\Version::VERSION);

        $this->container = $container;

        foreach(array('release.build', 'release.deploy', 'env.ssh', 'env.mysql') as $task) {
            $this->add(new \Zicht\Tool\Command\TaskCommand($this->container->get('task_builder')->build($task), $this->container));
        }
    }
}