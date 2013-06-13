<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Packager;

use \Symfony\Component\Console\Application as BaseApplication;
use \Symfony\Component\Yaml\Yaml;
use \Symfony\Component\Config\FileLocator;
use \Symfony\Component\Config\Definition\Processor;
use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;


/**
 * Z Packager application
 */
class Application extends BaseApplication
{
    /**
     * @{inheritDoc}
     */
    public function __construct($name = 'Zicht Tool packager', $version = 'development')
    {
        parent::__construct($name, $version);

        $this->add(new Cmd\BuildCommand());
    }
}