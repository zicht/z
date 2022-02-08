<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace ZichtTest\Tool\Configuration;

class FileLoaderTest extends \PHPUnit_Framework_TestCase
{
    function testLoadWithPlugins()
    {
        $locator = $this->createMock('Symfony\Component\Config\FileLocator');
        $loader = new \Zicht\Tool\Configuration\FileLoader($locator);
        $locator->expects($this->at(0))->method('locate')->with('valid/Plugin.php')->will($this->returnValue(
            __DIR__ . '/../assets/plugins/valid/Plugin.php'
        ));
        $locator->expects($this->at(1))->method('locate')->with('valid/z.yml')->will($this->throwException(new \InvalidArgumentException()));
        $yml = <<<EOSTR
plugins: ['valid']
EOSTR;
;
        $config = $loader->load($yml);
        $this->assertEquals(array(), $config);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    function testInvalidPluginWillThrowInvalidArgumentException()
    {
        $locator = $this->createMock('Symfony\Component\Config\FileLocator');
        $loader = new \Zicht\Tool\Configuration\FileLoader($locator);
        $locator->expects($this->at(0))->method('locate')->with('invalid/Plugin.php')->will($this->throwException(new \InvalidArgumentException()));
        $locator->expects($this->at(1))->method('locate')->with('invalid/z.yml')->will($this->throwException(new \InvalidArgumentException()));
        $yml = <<<EOSTR
plugins: ['invalid']
EOSTR;
;
        $loader->load($yml);
    }

    function testLoadWithPluginsWillLoadZFileOfPlugin()
    {
        $locator = $this->createMock('Symfony\Component\Config\FileLocator');
        $loader = new \Zicht\Tool\Configuration\FileLoader($locator);
        $locator->expects($this->at(0))->method('locate')->with('valid/Plugin.php')->will($this->returnValue(
            __DIR__ . '/../assets/plugins/valid/Plugin.php'
        ));
        $locator->expects($this->at(1))->method('locate')->with('valid/z.yml')->will($this->returnValue(
            __DIR__ . '/../assets/plugins/valid/z.yml'
        ));
        $yml = <<<EOSTR
plugins: ['valid']
EOSTR;
;
        $loader->load($yml);
        $this->assertEquals(array('valid' => __DIR__ . '/../assets/plugins/valid/Plugin.php'), $loader->getPlugins());
    }


    function testLoadWithImports()
    {
        $locator = $this->createMock('Symfony\Component\Config\FileLocator');
        $loader = new \Zicht\Tool\Configuration\FileLoader($locator);
        $locator->expects($this->at(0))->method('locate')->with('another/file.yml')->will($this->returnValue(
            __DIR__ . '/../assets/import/import.yml'
        ));
        $yml = <<<EOSTR
imports: ['another/file.yml']
EOSTR;
;
        $loader->load($yml);
        $configs = $loader->getConfigs();
        $this->assertEquals(array('imported-key' => 'imported-value'), array_shift($configs));
    }
}
