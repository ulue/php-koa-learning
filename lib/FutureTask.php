<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-12-11
 * Time: 15:32
 */

namespace Inhere\Koa;

/**
 * Class FutureTask
 * @package Inhere\Koa
 */
class FutureTask
{
    const PENDING = 1;
    const DONE = 2;
    const TIMEOUT = 3;

    private $timerId;
    private $cc;

    public $state;
    public $result;
    public $ex;

    // 我们这里加入新参数，用来链接futureTask与caller父任务
    // 这样的好处比如可以共享父子任务上下文
    public function __construct(\Generator $gen, AsyncTask $parent = null)
    {
        $this->state = self::PENDING;

        if ($parent) {
            $asyncTask = new AsyncTask($gen, $parent);
        } else {
            $asyncTask = new AsyncTask($gen);
        }

        $asyncTask->begin(function($r, $ex = null)  {
            // PENDING or TIMEOUT
            if ($this->state === self::TIMEOUT) {
                return;
            }

            // PENDING -> DONE
            $this->state = self::DONE;

            if ($cc = $this->cc) {
                if ($this->timerId) {
                    swoole_timer_clear($this->timerId);
                }
                $cc($r, $ex);
            } else {
                $this->result = $r;
                $this->ex = $ex;
            }
        });
    }

    // 这里超时时间0为永远阻塞，
    // 否则超时未获取到结果，将向父任务传递超时异常
    public function get($timeout = 0)
    {
        return call_cc(function($cc) use($timeout) {
            // PENDING or DONE
            if ($this->state === self::DONE) {
                $cc($this->result, $this->ex);
            } else {
                // 获取结果时未完成，保存$cc，开启定时器(如果需要)，挂起等待
                $this->cc = $cc;
                $this->getResultTimeout($timeout);
            }
        });
    }

    private function getResultTimeout($timeout)
    {
        if (!$timeout) {
            return;
        }

        $this->timerId = swoole_timer_after($timeout, function() {
            \assert($this->state === self::PENDING);
            $this->state = self::TIMEOUT;
            $cc = $this->cc;
            $cc(null, new AsyncTimeoutException());
        });
    }
}