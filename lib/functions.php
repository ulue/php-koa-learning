<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-12-11
 * Time: 14:25
 */

namespace Inhere\Koa;

/**
 * spawn one semicoroutine
 * @param mixed $task
 * @param array $args
 * @internal param callable|\Generator|mixed $task
 * @internal param callable $continuation function($r = null, $ex = null) {}
 * @internal param AsyncTask $parent
 * @internal param array $ctx Context也可以附加在 \Generator 对象的属性上
 *  第一个参数为task
 *  剩余参数(优先检查callable)
 *      如果参数类型 callable 则参数被设置为 Continuation
 *      如果参数类型 AsyncTask 则参数被设置为 ParentTask
 *      如果参数类型 array 则参数被设置为 Context
 */
function spawn ($task, ...$args) {
    $ctx = [];
    $parent = null;
    $continuation = function ($ret, $ex) {};

    foreach ($args as $arg) {
        if (\is_callable($arg)) {
            $continuation = $arg;
        } else if ($arg instanceof AsyncTask) {
            $parent = $arg;
        } else if (\is_array($arg)) {
            $ctx = $arg;
        }
    }

    if (\is_callable($task)) {
        try {
            $task = $task();
        } catch (\Exception $ex) {
            $continuation(null, $ex);
            return;
        }
    }

    if ($task instanceof \Generator) {
        foreach ($ctx as $k => $v) {
            $task->$k = $v;
        }

        (new AsyncTask($task, $parent))->begin($continuation);
    } else {
        $continuation($task, null);
    }
}

function await($task, ...$args)
{
    if ($task instanceof \Generator) {
        return $task;
    }

    if (\is_callable($task)) {
        $gen = function() use($task, $args) { yield $task(...$args); };
    } else {
        $gen = function() use($task) { yield $task; };
    }

    return $gen();
}

function race(array $tasks)
{
    $tasks = array_map(__NAMESPACE__ . '\\await', $tasks);

    return new SysCall(function(AsyncTask $parent) use($tasks) {
        if (empty($tasks)) {
            return null;
        }

        return new Any($tasks, $parent);
    });
}

function all(array $tasks)
{
    $tasks = array_map(__NAMESPACE__ . '\\await', $tasks);

    return new Syscall(function(AsyncTask $parent) use($tasks) {
        if (empty($tasks)) {
            return null;
        }

        return new All($tasks, $parent);
    });
}

function call_cc(callable $func) {
    return new CallCC($func);
}

function once(callable $func) {
    $has = false;

    return function(...$args) use($func, &$has) {
        if ($has === false) {
            $func(...$args);
            $has = true;
        }
    };
}

function timeout($ms)
{
    return call_cc(function($k) use($ms) {
        swoole_timer_after($ms, function() use($k) {
            $k(null, new \Exception('timeout'));
        });
    });
}
function go(...$args)
{
    spawn(...$args);
}

function chan($n = 0)
{
    if ($n === 0) {
        return new Channel();
    }

    return new BufferChannel($n);
}

function fork($task, ...$args)
{
    $task = await($task); // 将task转换为生成器

    return new Syscall(function(AsyncTask $parent) use($task) {
        return new FutureTask($task, $parent);
    });
}

function async_sleep($ms)
{
    return call_cc(function($k) use($ms) {
        swoole_timer_after($ms, function() use($k) {
            $k(null);
        });
    });
}

// function async_dns_lookup($host)
// {
//     return call_cc(function($k) use($host) {
//         swoole_async_dns_lookup($host, function($host, $ip) use($k) {
//             $k($ip);
//         });
//     });
// }

function async_dns_lookup($host, $timeout = 100)
{
    return race([
        call_cc(function($k) use($host) {
            swoole_async_dns_lookup($host, function($host, $ip) use($k) {
                $k($ip);
            });
        }),
        timeout($timeout),
    ]);
}