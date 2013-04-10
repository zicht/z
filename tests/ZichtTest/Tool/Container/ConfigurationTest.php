<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */
namespace ZichtTest\Tool\Container;

/**
 * @covers \Zicht\Tool\Configuration\Configuration
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider validFiles
     */
    function testValidConfigurations($input, $expectedOutput)
    {
        $processor = new \Symfony\Component\Config\Definition\Processor();
        $config = new \Zicht\Tool\Configuration\Configuration(array());
        $result = $processor->process($config->getConfigTreeBuilder()->buildTree(), array($input));
        $this->assertEquals($result, $expectedOutput);
    }

    function validFiles()
    {
        $ret = array();
        foreach (new \RegexIterator(new \DirectoryIterator(__DIR__ . '/../assets/valid-files'), '~.yml$~') as $file) {
            $file = realpath($file->getPathName());
            $php = dirname($file) . '/' . basename($file, '.yml') . '.php';
            $result = include $php;
            $ret[]= array(\Symfony\Component\Yaml\Yaml::parse($file), $result);
        }
        return $ret;
    }



    function testPluginLoaderHandling()
    {
        $plugin = $this->getMockBuilder('Zicht\Tool\PluginInterface')
            ->getMock()
        ;

        $config = new \Zicht\Tool\Configuration\Configuration(array($plugin));
        $self = $this;
        $plugin->expects($this->once())->method('appendConfiguration')->will($this->returnCallback(function($o) use($self) {
            $self->assertInstanceOf('Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition', $o);
        }));
        $config->getConfigTreeBuilder();
    }
}