<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-12-11
 * Time: 15:13
 */

use function Inhere\Koa\async_dns_lookup;
use Inhere\Koa\HttpClient;
use function Inhere\Koa\race;
use function Inhere\Koa\spawn;
use function Inhere\Koa\all;

require __DIR__ . '/autoload.php';

spawn(function() {
    $ex = null;
    try {
        $r = yield all([
            async_dns_lookup("www.bing.com", 100),
            async_dns_lookup("www.so.com", 100),
            async_dns_lookup("www.baidu.com", 100),
        ]);
        var_dump($r);

        /*
        array(3) {
          [0]=>
          string(14) "202.89.233.103"
          [1]=>
          string(14) "125.88.193.243"
          [2]=>
          string(15) "115.239.211.112"
        }
        */
    } catch (\Exception $ex) {
        echo $ex;
    }
});