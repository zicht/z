<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

require_once __DIR__ . '/../vendor/autoload.php';
/**
 * Points to the root of the Z installation
 */
define('ZPREFIX', __DIR__ . '/../');

// This part handles the case where the first argument is '-', which indicates shebang usage of Z.
if ($_SERVER['argc'] > 2 && $_SERVER['argv'][1] === '-') {
    $config = Zicht\Tool\Configuration\ConfigurationLoader::fromEnv($_SERVER['argv'][2]);
    array_splice($_SERVER['argv'], 1, 2);
} else {
    $config = Zicht\Tool\Configuration\ConfigurationLoader::fromEnv();
}
$app = new Zicht\Tool\Application('The Zicht Tool', (string) include ZPREFIX . '/version.php', $config);
$app->run();