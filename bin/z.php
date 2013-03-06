<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
require_once __DIR__ . '/../vendor/autoload.php';

function deprecation_decorator($err, $errstr) {
    fwrite(STDERR, "[DEPRECATED] $errstr\n");
}
set_error_handler('deprecation_decorator', E_USER_DEPRECATED);
$app = new Zicht\Tool\Application();
$app->run();