<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-12-08
 * Time: 18:54
 */

namespace Inhere\Koa\Async;

use Inhere\Koa\AsyncInterface;

/**
 * Class AsyncSleep - 定时器修改为标准异步接口
 * @package Inhere\Koa\Async
 */
class AsyncSleep implements AsyncInterface
{
    /** @var int  */
    private $sleepMs;

    public function __construct($ms = 1000)
    {
        $this->sleepMs = $ms;
    }

    /**
     * 开启异步任务，完成时执行回调，任务结果或异常通过回调参数传递
     * @param callable $callback
     *      continuation :: (mixed $result = null, \Exception|null $ex = null)
     * @return void
     */
    public function begin(callable $callback)
    {
        swoole_timer_after($this->sleepMs, $callback);
    }
}