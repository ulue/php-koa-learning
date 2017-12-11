<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-12-11
 * Time: 15:11
 */

namespace Inhere\Koa;

/**
 * Class All
 * - Any表示多个异步回调，任一回调完成则任务完成，All表示等待所有回调均执行完成才算任务完成，二者相同点是IO部分并发执行;
 * @package Inhere\Koa
 */
class All implements AsyncInterface
{
    public $parent;
    public $tasks;
    public $continuation;

    public $n;
    public $results;
    public $done;

    public function __construct(array $tasks, AsyncTask $parent = null)
    {
        $this->tasks = $tasks;
        $this->parent = $parent;
        $this->n = \count($tasks);
        \assert($this->n > 0);
        $this->results = [];
    }

    public function begin(callable $continuation = null)
    {
        $this->continuation = $continuation;
        foreach ($this->tasks as $id => $task) {
            (new AsyncTask($task, $this->parent))->begin($this->continuation($id));
        }
    }

    private function continuation($id)
    {
        return function($r, $ex = null) use($id) {
            if ($this->done) {
                return;
            }

            // 任一回调发生异常，终止任务
            if ($ex) {
                $this->done = true;
                $k = $this->continuation;
                $k(null, $ex);
                return;
            }

            $this->results[$id] = $r;

            if (--$this->n === 0) {
                // 所有回调完成，终止任务
                $this->done = true;
                if ($this->continuation) {
                    $k = $this->continuation;
                    $k($this->results);
                }
            }
        };
    }
}