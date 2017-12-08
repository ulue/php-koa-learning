<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-12-08
 * Time: 17:16
 * @link https://git-books.github.io/books/php-co-koa/?p=di-yi-bu-52063a-ban-xie-cheng-diao-du-qi/sheng-cheng-qi-die-dai.md
 */

use Inhere\Koa\AsyncTask;

require __DIR__ . '/autoload.php';

function new_gen()
{
    $r1 = (yield 1);
    $r2 = (yield '-2');

    echo $r1, $r2;
}

$task = new AsyncTask(new_gen());
$task->begin(); // output: 1-2