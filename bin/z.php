<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
require_once __DIR__ . '/../vendor/autoload.php';


set_error_handler(function($err, $errstr) {
    static $stack = array();
    if ($err & error_reporting()) {
        if (!in_array($errstr, $stack)) {
            fwrite(STDERR, "[$err] $errstr\n");
            $stack[]= $errstr;
        }
    }
});
$app = new Zicht\Tool\Application();
$app->run();