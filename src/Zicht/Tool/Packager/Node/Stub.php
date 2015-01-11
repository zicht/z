<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */
namespace Zicht\Tool\Packager\Node;

use \Zicht\Tool\Script\Buffer;
use Zicht\Version\Version;
use \Zicht\Tool\Script\Node\Node;

/**
 * Phar stub
 */
abstract class Stub implements Node
{
    protected $phar;
    protected $appName;
    protected $appVersion;

    /**
     * Writes the header stub to the phar.
     *
     * @param \Phar $phar
     * @param string $appName
     * @param string $appVersion
     */
    public function __construct(\Phar $phar, $appName, $appVersion)
    {
        $this->phar = $phar;
        $this->appName = $appName;
        $this->appVersion = $appVersion;
    }


    /**
     * Compiles into the specified buffer.
     *
     * @param \Zicht\Tool\Script\Buffer $buffer
     * @return void
     */
    final public function compile(Buffer $buffer)
    {
        $buffer
            ->writeln("Phar::mapPhar('z.phar');")
            ->writeln("define('ZPREFIX', 'phar://z.phar/');")
            ->writeln("require_once 'phar://z.phar/vendor/autoload.php';")
        ;

        $this->compileInitialization($buffer);
        $buffer
            ->writeln('$app->run();')
            ->writeln('__HALT_COMPILER();')
        ;
    }


    /**
     * Should compile the initialization code into the buffer.
     *
     * @param \Zicht\Tool\Script\Buffer $buffer
     * @return void
     */
    abstract protected function compileInitialization(Buffer $buffer);
}
