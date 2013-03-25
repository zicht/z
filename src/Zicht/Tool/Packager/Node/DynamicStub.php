<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */
namespace Zicht\Tool\Packager\Node;

use Zicht\Tool\Script\Buffer;

class DynamicStub extends Stub
{
    public function __construct(\Phar $phar, $appName, $appVersion, $configFileName)
    {
        parent::__construct($phar, $appName, $appVersion);
        $this->configFilename = $configFileName;
    }


    function compileInitialization(Buffer $buffer)
    {
        $buffer->write('$app = new Zicht\Tool\Application(')
            ->asPhp($this->appName)
            ->raw(', ')
            ->asPhp($this->appVersion)
            ->raw(', Zicht\Tool\Configuration\ConfigurationLoader::fromEnv(')->asPhp($this->configFilename)->raw(')')
            ->raw(');')
            ->eol();
    }

}