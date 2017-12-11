<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-12-11
 * Time: 15:25
 */
// 构建两个单向channel, 我们只单向收发数据

use function Inhere\Koa\async_sleep;
use function Inhere\Koa\chan;
use function Inhere\Koa\go;

require __DIR__ . '/autoload.php';

$pingCh = chan();
$pongCh = chan();

go(function() use($pingCh, $pongCh) {
    while (true) {
        echo (yield $pingCh->recv());
        yield $pongCh->send("PONG\n");

        // 递归调度器实现，需要引入异步的方法退栈，否则Stack Overflow...
        // 或者考虑将send或者recv以defer方式实现
        yield async_sleep(1);
    }
});

go(function() use($pingCh, $pongCh) {
    while (true) {
        echo (yield $pongCh->recv());
        yield $pingCh->send("PING\n");

        yield async_sleep(1);
    }
});

// start up
go(function() use($pingCh) {
    echo "start up\n";
    yield $pingCh->send("PING");
});
