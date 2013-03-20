<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Emits deprecation warnings to stderr.
 *
 * @param int $err
 * @param string $errstr
 */
function deprecation_decorator($err, $errstr)
{
    static $repeating = array();
    if (in_array($errstr, $repeating)) {
        return;
    }
    $repeating[]= $errstr;
    fwrite(STDERR, "[DEPRECATED] $errstr\n");
}
set_error_handler('deprecation_decorator', E_USER_DEPRECATED);

/**
 * Points to the root of the Z installation
 */
define('ZPREFIX', __DIR__ . '/../');

$app = new Zicht\Tool\Application();
$app->run();