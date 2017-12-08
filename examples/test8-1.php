<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-12-08
 * Time: 19:41
 */

use Inhere\Koa\AsyncTask;

require __DIR__ . '/autoload.php';

function tt()
{
    yield;
    throw new \Exception("e");
}

function t()
{
    yield tt();
    yield 1;
}

$task = new AsyncTask(t());

$trace = function($r, $ex) {
    if ($ex) {
        echo $ex->getMessage(); // output: e
    } else {
        echo $r;
    }
};

$task->begin($trace);