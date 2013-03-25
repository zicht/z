<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Packager\Node;

use Zicht\Tool\Script\Buffer;

class StaticStub extends Stub
{
    public function __construct(\Phar $phar, $appName, $appVersion, $staticConfig, $staticPluginPaths)
    {
        parent::__construct($phar, $appName, $appVersion);

        $this->staticConfig = $staticConfig;
        $this->staticPluginPaths = $staticPluginPaths;
    }


    function compileInitialization(Buffer $buffer)
    {
        $configurationLoader = new \Zicht\Tool\Configuration\ConfigurationLoader(
            $this->staticConfig,
            new \Symfony\Component\Config\FileLocator(array(getcwd())),
            new \Symfony\Component\Config\FileLocator($this->staticPluginPaths)
        );

        $compiler = new \Zicht\Tool\Container\ContainerCompiler($configurationLoader->processConfiguration());

//        foreach ($this->loader->getPlugins() as $plugin) {
//            $c = new \ReflectionClass(get_class($plugin));
//
//        }
        $this->phar['container.php'] = $compiler->getContainerCode();
        $buffer->writeln('$container = require_once \'phar://z.phar/container.php\';');
        $buffer->writeln('$app = new Zicht\Tool\Application(')->asPhp($this->appName)->raw(', ')->asPhp($this->appVersion)->raw(');')->eol();
        $buffer->writeln('$app->setContainer($container);');
    }
}