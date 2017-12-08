<?php

namespace Inhere\Koa;

/**
 * Interface AsyncInterface
 * @package Inhere\Koa
 */
interface AsyncInterface
{
    /**
     * 开启异步任务，完成时执行回调，任务结果或异常通过回调参数传递
     * @param callable $callback
     *      continuation :: (mixed $result = null, \Exception|null $ex = null)
     * @return void
     */
    public function begin(callable $callback);
}
