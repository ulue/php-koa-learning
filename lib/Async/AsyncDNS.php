<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-12-08
 * Time: 18:56
 */

namespace Inhere\Koa\Async;

use Inhere\Koa\AsyncInterface;

/**
 * Class AsyncDNS
 * @package Inhere\Koa\Async
 */
class AsyncDNS implements AsyncInterface
{
    /**
     * @var string
     */
    private $domain;

    /**
     * AsyncDNS constructor.
     * @param string $domain e.g 'www.baidu.com'
     */
    public function __construct($domain)
    {
        $this->domain = $domain;
    }

    /**
     * 开启异步任务，完成时执行回调，任务结果或异常通过回调参数传递
     * @param callable $callback
     *      continuation :: (mixed $result = null, \Exception|null $ex = null)
     * @return void
     */
    public function begin(callable $callback)
    {
        swoole_async_dns_lookup($this->domain, function ($host, $ip) use ($callback) {
            // 这里我们会发现， 通过call $callback， 将返回值作为参数进行传递， 与 call $callback 相像
            // $ip 通过 $callback 从子生成器传入父生成器， 最终通过send方法成为yield表达式结果
            $callback($ip);
        });
    }
}