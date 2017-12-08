<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-12-08
 * Time: 17:16
 * @link https://git-books.github.io/books/php-co-koa/?p=di-yi-bu-52063a-ban-xie-cheng-diao-du-qi/sheng-cheng-qi-die-dai.md
 */

use Inhere\Koa\Async\AsyncDNS;
use Inhere\Koa\Async\AsyncSleep;
use Inhere\Koa\AsyncTask;

require __DIR__ . '/autoload.php';

function newSubGen()
{
    yield -1;
    yield 1;
}

function newGen()
{
    $r1 = (yield newSubGen());
    $r2 = (yield 2);
    $start = time();

    yield new AsyncSleep();

    echo time() - $start, "\n";

    $ip = (yield new AsyncDNS('www.baidu.com'));

    yield "IP: $ip";
}
$task = new AsyncTask(newGen());

$trace = function($r) { echo $r; };

$task->begin($trace);
// output:
// 1
// IP: 115.239.210.27
