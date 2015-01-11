<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */
namespace Zicht\Tool\Packager\Node;

use \Zicht\Tool\Script\Buffer;

/**
 * PHAR stub for a dynamic Z build
 */
class DynamicStub extends Stub
{
    /**
     * Construct the stub with the specified details
     *
     * @param \Phar $phar
     * @param string $appName
     * @param string $appVersion
     * @param string $configFileName
     */
    public function __construct(\Phar $phar, $appName, $appVersion, $configFileName)
    {
        parent::__construct($phar, $appName, $appVersion);
        $this->configFilename = $configFileName;
    }


    /**
     * Writes the initialization code for a dynamic build
     *
     * @param \Zicht\Tool\Script\Buffer $buffer
     * @return void
     */
    protected function compileInitialization(Buffer $buffer)
    {
        $buffer->write('$app = new Zicht\Tool\Application(')
            ->asPhp($this->appName)
            ->raw(', Zicht\Version\Version::fromString(')
            ->asPhp($this->appVersion)
            ->raw(') ?: new Zicht\Version\Version(), Zicht\Tool\Configuration\ConfigurationLoader::fromEnv(')->asPhp($this->configFilename)->raw(')')
            ->raw(');')
            ->eol();
    }
}