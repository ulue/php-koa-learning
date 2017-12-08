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

    /** @var callable */
    public $continuation;

    /**
     * AsyncTask constructor.
     * @param \Generator $generator
     */
    public function __construct(\Generator $generator)
    {
        $this->gen = new GenWrapper($generator);
    }

    /**
     * @param callable $continuation
     */
    public function begin(callable $continuation)
    {
        $this->continuation = $continuation;

        $this->next();
    }

    /**
     * @param null|mixed $result
     */
    public function next($result = null)
    {
        $value = $this->gen->send($result);

        if ($this->gen->valid()) {
            // \Generator -> AsyncInterface
            if ($value instanceof \Generator) {
                $value = new self($value);
            }

            if ($value instanceof AsyncInterface) {
                // 父任务next方法是子任务的延续，子任务迭代完成后继续完成父任务迭代
                $continuation = [$this, 'next'];

                $value->begin($continuation);
            } else {
                $this->next($value);
            }
        } else {
            $cb = $this->continuation;
            $cb($result);
        }
    }
}