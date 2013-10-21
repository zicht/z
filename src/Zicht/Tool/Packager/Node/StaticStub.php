<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Packager\Node;

use \Symfony\Component\Config\FileLocator;
use \Zicht\Tool\Configuration;
use \Zicht\Tool\Container\ContainerCompiler;
use \Zicht\Tool\Script\Buffer;

/**
 * PHAR Stub implementation for a static build
 */
class StaticStub extends Stub
{
    /**
     * Construct the stub with the specified details
     *
     * @param \Phar $phar
     * @param string $appName
     * @param string $appVersion
     * @param array $staticConfig
     * @param array $staticPluginPaths
     */
    public function __construct(\Phar $phar, $appName, $appVersion, $staticConfig, $staticPluginPaths)
    {
        parent::__construct($phar, $appName, $appVersion);

        $this->staticConfig = $staticConfig;
        $this->staticPluginPaths = $staticPluginPaths;
    }


    /**
     * Writes the initialization code for a static build of Z.
     *
     * @param \Zicht\Tool\Script\Buffer $buffer
     * @return mixed|void
     */
    protected function compileInitialization(Buffer $buffer)
    {
        $configurationLoader = new Configuration\ConfigurationLoader(
            $this->staticConfig,
            new FileLocator(array(getcwd())),
            new Configuration\FileLoader(
                new Configuration\PathDefaultFileLocator(
                    'ZPLUGINPATH',
                    $this->staticPluginPaths
                )
            )
        );

        $compiler = new ContainerCompiler($configurationLoader->processConfiguration());
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
        $buffer
            ->writeln('Zicht\Tool\Application::$HEADER = \'\';')
            ->write('$app = new Zicht\Tool\Application(')
            ->asPhp($this->appName)
            ->raw(', ')
            ->asPhp($this->appVersion)
            ->raw(');')
            ->eol()
        ;
        $buffer->writeln('$app->setContainer($container);');
    }
}