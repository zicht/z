<?php

$init = function() {
    foreach (array('/../vendor/', '/../../../') as $relativeBasePath) {
        $fn = __DIR__ . $relativeBasePath . 'autoload.php';
        if (is_file($fn)) {
            require_once $fn;
            return true;
        }
    }
    return false;
};
if (!$init()) {
    trigger_error("The autoloader could not be located", E_USER_ERROR);
    exit(169);
}
unset($init);

