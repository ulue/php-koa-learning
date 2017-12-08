<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-12-08
 * Time: 19:41
 */

use Inhere\Koa\AsyncTask;

require __DIR__ . '/autoload.php';

function newSubGen()
{
    yield 0;
    throw new \Exception('ex');
    yield 1;
}

function newGen()
{
    try {
        $r1 = (yield newSubGen());
    } catch (\Exception $ex) {
        echo $ex->getMessage(); // output: e
    }
    $r2 = (yield 2);
    yield 3;
}
$task = new AsyncTask(newGen());
$trace = function($r, $ex) {
    if ($ex) {
        echo $ex->getMessage();
    } else {
        echo $r; // output: 3
    }
};
$task->begin($trace); // output: e