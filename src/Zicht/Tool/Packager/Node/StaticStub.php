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
        $this->phar['container.php'] = $compiler->getContainerCode();
        $buffer->writeln('$container = require_once \'phar://z.phar/container.php\';');

        foreach ($configurationLoader->getPlugins() as $name => $plugin) {
            $className = get_class($plugin);
            $embeddedFilename = 'plugins/' . $name . '.php';

            $class = new \ReflectionClass($className);

            $this->phar[$embeddedFilename] = file_get_contents($class->getFileName());
            $buffer->write('require_once ')->asPhp('phar://z.phar/' . $embeddedFilename)->raw(';')->eol();
            $buffer->write('$p = new ')->write($className)->raw('();')->eol();
            $buffer->writeln('$p->setContainer($container);');
        }
        $buffer->write('$app = new Zicht\Tool\Application(')->asPhp($this->appName)->raw(', ')->asPhp($this->appVersion)->raw(');')->eol();
        $buffer->writeln('$app->setContainer($container);');
    }
}