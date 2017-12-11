<?php
/**
 * ./tests/test.sh
 * OR
 * phpunit6.phar --bootstrap tests/boot.php tests
 */

error_reporting(E_ALL | E_STRICT);
date_default_timezone_set('Asia/Shanghai');

include dirname(__DIR__) . '/lib/functions.php';

spl_autoload_register(function ($class) {
    $file = null;

    if (0 === strpos($class,'Inhere\Koa\Examples\\')) {
        $path = str_replace('\\', '/', substr($class, strlen('Inhere\Koa\Examples\\')));
        $file = dirname(__DIR__) . "/{$path}.php";
    } elseif (0 === strpos($class,'Inhere\Koa\\')) {
        $path = str_replace('\\', '/', substr($class, strlen('Inhere\Koa\\')));
        $file = dirname(__DIR__) . "/lib/{$path}.php";
    }

    if ($file && is_file($file)) {
        include $file;
    }
});