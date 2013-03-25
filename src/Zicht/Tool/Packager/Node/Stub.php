<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */
namespace Zicht\Tool\Packager\Node;
use \Zicht\Tool\Script\Buffer;
use \Zicht\Tool\Script\Node\Node;

abstract class Stub implements Node
{
    public function __construct(\Phar $phar, $appName, $appVersion)
    {
        $this->phar = $phar;
        $this->appName = $appName;
        $this->appVersion = $appVersion;
    }

    final function compile(Buffer $buffer)
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


    abstract function compileInitialization(Buffer $buffer);
}
