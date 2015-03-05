<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Container;

use \Zicht\Tool\Script\Buffer;
use \Zicht\Tool\PluginInterface;

/**
 * Compiler to compile the entire container into PHP code.
 */
class ContainerCompiler
{
    private $configTree;
    private $plugins;
    private $file;
    private $code;


    /**
     * Construct the compiler
     *
     * @param array $configTree
     * @param PluginInterface[] $plugins
     * @param null $file
     */
    public function __construct($configTree, $plugins, $file = null)
    {
        $this->configTree = $configTree;
        $this->plugins = $plugins;
        if (null === $file) {
            $file = tempnam(sys_get_temp_dir(), 'z');
        }
        $this->file = $file;
    }


    /**
     * Writes the code to a temporary file and returns the resulting Container object.
     *
     * @return mixed
     *
     * @throws \LogicException
     */
    public function getContainer()
    {
        $this->code = $this->compileContainerCode();

        file_put_contents($this->file, $this->code);
        $ret = include $this->file;
        unlink($this->file);

        if (! ($ret instanceof Container)) {
            throw new \LogicException("The container must be returned by the compiler");
        }

        foreach ($this->plugins as $plugin) {
            $ret->addPlugin($plugin);
        }
        return $ret;
    }


    /**
     * Add a plugin
     *
     * @param \Zicht\Tool\PluginInterface $p
     * @return void
     */
    public function addPlugin(PluginInterface $p)
    {
        $this->plugins[]= $p;
    }


    /**
     * Returns the code for initializing the container.
     *
     * @return string
     */
    public function compileContainerCode()
    {
        $builder = new ContainerBuilder($this->configTree);
        foreach ($this->plugins as $name => $plugin) {
            $plugin->setContainerBuilder($builder);
        }
        $containerNode = $builder->build();
        $buffer = new Buffer();

        $buffer->write('<?php')->eol();
        $containerNode->compile($buffer);
        $buffer->writeln('return $z;');
        $code = $buffer->getResult();
        return $code;
    }
}