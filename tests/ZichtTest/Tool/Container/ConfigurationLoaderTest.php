<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace ZichtTest\Tool\Container;

use Zicht\Tool\Configuration\ConfigurationLoader;

class ConfigurationLoaderTest extends \PHPUnit_Framework_TestCase
{
    function testConstruction()
    {
        $this->createLoader();
    }

    public function createLoader()
    {
        $this->fileLoader = $this->getMock('Zicht\Tool\Configuration\FileLoader', array(), array($this->getMock('Symfony\Component\Config\FileLocator')));
        $this->fileLocator = $this->getMock('Symfony\Component\Config\FileLocator');
        return new ConfigurationLoader(
            'file.yml',
            $this->fileLocator,
            $this->fileLoader
        );
    }


    function testProcessConfiguration()
    {
        $loader = $this->createLoader();

        $this->fileLocator->expects($this->once())->method('locate')->with('file.yml')->will($this->returnValue(array('some/file.yml')));
        $this->fileLoader->expects($this->once())->method('getConfigs')->will($this->returnValue(array(
            array()
        )));
        $this->fileLoader->expects($this->once())->method('getPlugins')->will($this->returnValue(array()));
        $loader->processConfiguration();
    }


    function testProcessConfigurationWillTriggerLoadingPlugins()
    {
        $loader = $this->createLoader();

        $this->fileLocator->expects($this->once())->method('locate')->with('file.yml')->will($this->returnValue(array('some/file.yml')));
        $this->fileLoader->expects($this->once())->method('getConfigs')->will($this->returnValue(array(
            array()
        )));
        $plugins = array('valid' => __DIR__ . '/../assets/plugins/valid/Plugin.php');
        $this->fileLoader->expects($this->once())->method('getPlugins')->will($this->returnValue($plugins));
        $loader->processConfiguration();

        $loadedPlugins = $loader->getPlugins();
        $this->assertInstanceOf('Zicht\tool\Plugin\Valid\Plugin', $loadedPlugins['valid']);
    }


    /**
     * @expectedException \UnexpectedValueException
     */
    function testProcessConfigurationWillThrowExceptionIfPluginIsNotValid()
    {
        $loader = $this->createLoader();

        $this->fileLocator->expects($this->once())->method('locate')->with('file.yml')->will($this->returnValue(array('some/file.yml')));
        $plugins = array('invalid' => __DIR__ . '/../assets/plugins/invalid/Plugin.php');
        $this->fileLoader->expects($this->once())->method('getPlugins')->will($this->returnValue($plugins));
        $loader->processConfiguration();
    }

    /**
     */
    function testProcessConfigurationWillDefaultToEmptyConfigIfFileNotFound()
    {
        $loader = $this->createLoader();

        $this->fileLocator->expects($this->once())->method('locate')->with('file.yml')->will($this->throwException(new \InvalidArgumentException()));
        $this->fileLoader->expects($this->once())->method('getPlugins')->will($this->returnValue(array()));
        $this->fileLoader->expects($this->once())->method('getConfigs')->will($this->returnValue(array(array())));
        $loader->processConfiguration();
    }
}