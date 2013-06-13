<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Container;

use \Zicht\Tool\Script\Buffer;

/**
 * Compiler to compile the entire container into PHP code.
 */
class ContainerCompiler
{
    /**
     * Construct the compiler
     *
     * @param array $configTree
     * @param null $file
     */
    public function __construct($configTree, $file = null)
    {
        $this->configTree = $configTree;
        if (null === $file) {
            $file = tempnam(sys_get_temp_dir(), 'z');
        }
        $this->file = $file;
    }


    /**
     * Writes the code to a temporary file and returns the resulting Container object.
     *
     * @return mixed
     */
    public function getContainer()
    {
        $code = $this->getContainerCode();

        file_put_contents($this->file, $code);

        $ret = include $this->file;
        unlink($this->file);
        return $ret;
    }


    /**
     * Returns the code for initializing the container.
     *
     * @return string
     */
    public function getContainerCode()
    {
        $builder = new ContainerBuilder($this->configTree);
        $containerNode = $builder->build();
        $buffer = new Buffer();

        $buffer->write('<?php')->eol();
        $containerNode->compile($buffer);
        $buffer->writeln('return $z;');
        $code = $buffer->getResult();
        return $code;
    }
}