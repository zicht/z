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

$app = new Zicht\Tool\Application(
    'The Zicht Tool',
    '1.2-dev',
    Zicht\Tool\Configuration\ConfigurationLoader::fromEnv()
);

$app->run();