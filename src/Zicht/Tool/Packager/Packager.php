<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Tool\Packager;

class Packager
{
    function __construct($root, $options)
    {
        $this->srcRoot = $root;
        $this->options = $options + array(
            'config-filename'   => 'z.yml',
            'app-name'          => 'The Zicht Tool',
            'app-version'       => 'development build (' . date('r') . ')',
            'static'            => false,
        );
    }


    function package($targetFile, $force)
    {
        if (is_file($targetFile)) {
            if ($force) {
                unlink($targetFile);
            } else {
                throw new \RuntimeException("File {$targetFile} already exists");
            }
        }

        $curDir = getcwd();
        chdir($this->srcRoot);

        $buildFile = 'build.phar';

        $phar = new \Phar($buildFile);
        foreach (array('vendor', 'src') as $dir) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir)) as $file) {
                if (!$file->isFile()) {
                    continue;
                }
                $phar[$file->getPathname()] = file_get_contents($file->getPathname());
            }
        }
        $phar['LICENSE'] = file_get_contents('LICENSE');

        $version        = var_export($this->options['app-version'], true);
        $appName        = var_export($this->options['app-name'], true);
        $configFilename = var_export($this->options['config-filename'], true);

        if ($static = $this->options['static']) {

        }

        $stub =<<<EOF
#!/usr/bin/env php
<?php
/**
 * Copyright (C) 2013 Zicht Online, Gerard van Helden
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit
 * persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS
 * OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
 * OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

/**
 * This file was built with the Z packager. For more information, visit the Z website at http://z.zicht.nl/
 *
 * Please pay your respects by at least leaving these notices in tact.
 */

Phar::mapPhar('z.phar');
define('ZPREFIX', 'phar://z.phar/');
require_once 'phar://z.phar/vendor/autoload.php';
\$app = new Zicht\Tool\Application($appName, $version, $configFilename);
Zicht\Tool\Application::\$HEADER = '';
\$app->run();
__HALT_COMPILER();
EOF;
        $phar->setStub($stub);

        rename($buildFile, $targetFile);
        chmod($targetFile, 0755);
        chdir($curDir);
    }
}