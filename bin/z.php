<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */


if (is_file(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    require_once __DIR__ . '/../../../autoload.php';
}
/**
 * Points to the root of the Z installation
 */
define('ZPREFIX', __DIR__ . '/../');
/**
 * Refers to the "binary"
 */
define('ZBIN', __FILE__);

$version = include ZPREFIX . '/version.php';

// This part handles the case where the first argument is '-', which indicates shebang usage of Z.
if ($_SERVER['argc'] > 2 && $_SERVER['argv'][1] === '-') {
    $config = Zicht\Tool\Configuration\ConfigurationLoader::fromEnv($_SERVER['argv'][2], $version);
    array_splice($_SERVER['argv'], 1, 2);
} else {
    $config = Zicht\Tool\Configuration\ConfigurationLoader::fromEnv(null, $version);
}
$app = new Zicht\Tool\Application('The Zicht Tool', $version, $config);
$app->run();
