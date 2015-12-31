<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Packager;

use \Symfony\Component\Console\Application as BaseApplication;

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

        throw new \RuntimeException("Sorry, the packager is currently unsupported");

        $this->add(new Cmd\BuildCommand());
    }
}