<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-12-08
 * Time: 17:10
 */

namespace Inhere\Koa;

/**
 * Class AsyncTask
 * @package Inhere\Koa
 */
final class AsyncTask implements AsyncInterface
{
    /** @var GenWrapper */
    public $gen;

    /** @var self */
    public $parent;

    /** @var callable */
    public $continuation;

    /**
     * AsyncTask constructor.
     * @param \Generator $generator
     * @param AsyncTask|null $parent
     * - 我们在构造器添加 $parent 参数， 把父子生成器链接起来，使其可以进行回溯
     */
    public function __construct(\Generator $generator, self $parent = null)
    {
        $this->gen = new GenWrapper($generator);
        $this->parent = $parent;
    }

    /**
     * @param callable $continuation
     * @throws \Exception
     */
    public function begin(callable $continuation)
    {
        $this->continuation = $continuation;

        $this->next();
    }

    /**
     * @param null|mixed $result
     * @param \Exception|null $ex
     * @throws \Exception
     */
    public function next($result = null, \Exception $ex = null)
    {
        try {
            if ($ex) {
                $value = $this->gen->throw($ex);
            } else {
                $value = $this->gen->send($result);
            }

            if ($this->gen->valid()) {
                if ($value instanceof SysCall) {
                    $value = $value($this);
                }

                // \Generator -> AsyncInterface
                if ($value instanceof \Generator) {
                    $value = new self($value, $this);
                }

                if ($value instanceof AsyncInterface) {
                    // 父任务next方法是子任务的延续，子任务迭代完成后继续完成父任务迭代
                    $continuation = [$this, 'next'];

                    $value->begin($continuation);
                } else {
                    $this->next($value);
                }
            } else {
                // 迭代结束 返回结果
                // cb 指向 父生成器next方法 或 用户传入continuation
                $cb = $this->continuation;
                $cb($result, null);
            }
        } catch (\Exception $ex){
            // 抛出异常
            if ($this->gen->valid()) {
                $this->next(null, $ex);

                // 未捕获异常
            } else {
                // cb 指向 父生成器next方法 或 用户传入continuation
                $cb = $this->continuation;
                $cb($result, $ex);
            }
        }
    }
}