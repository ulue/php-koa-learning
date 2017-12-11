<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-12-11
 * Time: 15:01
 */

use function Inhere\Koa\async_dns_lookup;
use Inhere\Koa\HttpClient;
use function Inhere\Koa\race;
use function Inhere\Koa\spawn;
use function Inhere\Koa\timeout;

require __DIR__ . '/autoload.php';

// 当我们采取race语义并发执行dns查询与超时异常函数
// 其实我们构造了一个更为灵活的超时处理方案
spawn(function() {
    try {
        $ip = yield race([
            async_dns_lookup("www.baidu.com"),
            timeout(100),
        ]);

        $res = yield race([
            (new HttpClient($ip, 80))->awaitGet("/"),
            timeout(200),
        ]);

        var_dump($res->statusCode);
    } catch (\Exception $ex) {
        echo $ex;
        swoole_event_exit();
    }
});