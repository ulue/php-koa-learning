<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-12-11
 * Time: 13:55
 */

namespace Inhere\Koa;

/**
 * Class SysCall
 * @package Inhere\Koa
 */
class SysCall
{
    /** @var callable */
    private $func;

    public function __construct(callable $func)
    {
        $this->func = $func;
    }

    /**
     * @param AsyncTask $task
     * @return mixed
     */
    public function __invoke(AsyncTask $task)
    {
        $func = $this->func;

        return $func($task);
    }

    /**
     * system call methods
     */

    /**
     * @param string $key
     * @param null|mixed $default
     * @return SysCall
     */
    public static function getCtx($key, $default = null)
    {
        return new self(function (AsyncTask $task) use($key, $default) {
            /** @var AsyncTask $task */
            while($task->parent && $task = $task->parent);

            if (isset($task->gen->generator->$key)) {
                return $task->gen->generator->$key;
            }

            return $default;
        });
    }

    public static function setCtx($key, $val)
    {
        return new self(function (AsyncTask $task) use($key, $val) {
            while($task->parent && $task = $task->parent);

            $task->gen->generator->$key = $val;
        });
    }
}