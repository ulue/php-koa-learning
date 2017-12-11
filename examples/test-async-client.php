<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-12-11
 * Time: 14:43
 */

use function Inhere\Koa\async_dns_lookup;
use Inhere\Koa\HttpClient;
use function Inhere\Koa\spawn;

require __DIR__ . '/autoload.php';

// 这里!
spawn(function() {
    $ip = (yield async_dns_lookup('www.baidu.com'));
    $cli = new HttpClient($ip, 80);
    $cli->setHeaders(["foo" => "bar"]);
    $cli = (yield $cli->asyncGet("/"));
    echo $cli->body, "\n";
});