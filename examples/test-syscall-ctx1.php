<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-12-11
 * Time: 14:09
 */

use Inhere\Koa\AsyncTask;
use Inhere\Koa\SysCall;

require __DIR__ . '/autoload.php';

$trace = function($r, $ex) {
    if ($ex instanceof \Exception) {
        echo 'cc_ex:' . $ex->getMessage(), "\n";
    } else {
        echo $r;
    }
};

function setTask()
{
    yield SysCall::setCtx("foo", "bar");
}

function ctxTest()
{
    yield setTask();
    $foo = (yield SysCall::getCtx("foo"));
    echo $foo;
}

$task = new AsyncTask(ctxTest());
$task->begin($trace); // output: bar