<?php

define('BELLTOLL_ROOT', __DIR__);

// Find where the composer autoload is
// This tool was installed as a composed dependency or directly
$root = realpath(__DIR__);
$autoloadLocations = array(
    __DIR__ . '/../../autoload.php',
    $root . DIRECTORY_SEPARATOR . 'vendor/autoload.php',
);
foreach ($autoloadLocations as $file) {
    if (file_exists($file)) {
        define('BELLTOLL_COMPOSER_AUTOLOAD', $file);
        break;
    }
}
// Composer autoload require guard
if (!defined('BELLTOLL_COMPOSER_AUTOLOAD')) {
    die(
        "You must run the command `composer install` from the terminal "
        . "in the directory '$root' before using this tool.\n"
    );
}
// Load composer autoloader
$autoload = require_once BELLTOLL_COMPOSER_AUTOLOAD;
// This is defined in composer.json
//$autoload->add('Belltoll', $root . '/src');

return $autoload;
