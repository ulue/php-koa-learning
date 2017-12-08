<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-12-08
 * Time: 19:58
 */

use Inhere\Koa\AsyncInterface;
use Inhere\Koa\AsyncTask;

require __DIR__ . '/autoload.php';

$trace = function($r, $ex) {
    if ($ex instanceof \Exception) {
        echo "cc_ex:" . $ex->getMessage(), "\n";
    }
};

if (!\function_exists('swoole_timer_after')) {
    function swoole_timer_after($time, $cb) {
        sleep((int)($time/1000));
        $cb();
    }
}

class AsyncException implements AsyncInterface
{
    public function begin(callable $cc)
    {
        swoole_timer_after(1000, function() use($cc) {
            $cc(null, new \Exception('timeout'));
        });
    }
}

function newSubGen()
{
    yield 0;
    $async = new AsyncException();
    yield $async;
}

function newGen($try)
{
    $start = time();
    try {
        $r1 = (yield newSubGen());
    } catch (\Exception $ex) {
        // 捕获subgenerator抛出的异常
        if ($try) {
            echo "catch:" . $ex->getMessage(), "\n";
        } else {
            throw $ex;
        }
    }
    echo time() - $start, "\n";
}

// 内部try-catch异常
$task = new AsyncTask(newGen(true));
$task->begin($trace);
// output:
// catch:timeout
// 1

echo "-------------------------------------\n";

// 异常传递至AsyncTask的最终回调
$task = new AsyncTask(newGen(false));
$task->begin($trace);
// output:
// cc_ex:timeout
