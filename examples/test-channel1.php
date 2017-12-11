<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-12-11
 * Time: 15:28
 */

use function Inhere\Koa\async_sleep;
use function Inhere\Koa\chan;
use function Inhere\Koa\go;

require __DIR__ . '/autoload.php';

$ch = chan();

// 生产者1
go(function() use($ch) {
    while (true) {
        yield $ch->send("producer 1");
        yield async_sleep(1000);
    }
});

// 生产者2
go(function() use($ch) {
    while (true) {
        yield $ch->send("producer 2");
        yield async_sleep(1000);
    }
});

// 消费者1
go(function() use($ch) {
    while (true) {
        $recv = (yield $ch->recv());
        echo "consumer1: $recv\n";
    }
});

// 消费者2
go(function() use($ch) {
    while (true) {
        $recv = (yield $ch->recv());
        echo "consumer2: $recv\n";
    }
});