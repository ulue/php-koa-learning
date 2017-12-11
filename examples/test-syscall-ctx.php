<?php

interface Async
{
    /**
     * 开启异步任务，完成时执行回调，任务结果或异常通过回调参数传递
     * @param callable $callback
     *      continuation :: (mixed $result = null, \Exception|null $ex = null)
     * @return void
     */
    public function begin(callable $callback);
}
class Gen
{
    /** @var bool */
    private $isFirst = true;

    /** @var \Generator */
    public $generator;

    /**
     * Generator constructor.
     * @param \Generator $generator
     */
    public function __construct(\Generator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * @param null|mixed $value
     * @return mixed
     */
    public function send($value = null)
    {
        if ($this->isFirst) {
            $this->isFirst = false;

            return $this->generator->current();
        }

        return $this->generator->send($value);
    }

    /**
     * @param \Exception $e
     * @return mixed
     */
    public function throw_(\Exception $e)
    {
        return $this->generator->throw($e);
    }

    /**
     * @return bool
     */
    public function valid()
    {
        if (!$this->generator) {
            return false;
        }

        return $this->generator->valid();
    }

    /**
     * @return bool
     */
    public function isFirst(): bool
    {
        return $this->isFirst;
    }
}
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

final class AsyncTask implements Async
{
    public $gen;
    public $continuation;
    public $parent;

    // 我们在构造器添加$parent参数， 把父子生成器链接起来，使其可以进行回溯
    public function __construct(\Generator $gen, AsyncTask $parent = null)
    {
        $this->gen = new Gen($gen);
        $this->parent = $parent;
    }

    public function begin(callable $continuation)
    {
        $this->continuation = $continuation;
        $this->next();
    }

    public function next($result = null, $ex = null)
    {
        try {
            if ($ex) {
                $value = $this->gen->throw_($ex);
            } else {
                $value = $this->gen->send($result);
            }

            if ($this->gen->valid()) {
                // 这里注意优先级， Syscall 可能返回\Generator 或者 Async
                if ($value instanceof SysCall) { // Syscall 签名见下方
                    $value = $value($this);
                }

                if ($value instanceof \Generator) {
                    $value = new self($value, $this);
                }

                if ($value instanceof Async) {
                    $cc = [$this, "next"];
                    $value->begin($cc);
                } else {
                    $this->next($value, null);
                }
            } else {
                $cc = $this->continuation;
                $cc($result, null);
            }
        } catch (\Exception $ex) {
            if ($this->gen->valid()) {
                $this->next(null, $ex);
            } else {
                $cc = $this->continuation;
                $cc($result, $ex);
            }
        }
    }
}

/**
 * test
 */

$trace = function($r, $ex) {
    if ($ex instanceof \Exception) {
        echo 'cc_ex:' . $ex->getMessage(), "\n";
    } else {
        // echo $r;
    }
};

function setTask()
{
    yield SysCall::setCtx("foo", "bar");
}

function ctxTest()
{
    yield setTask();
    $foo = (yield SysCall::getCtx("foo"));
    echo $foo;
}

$task = new AsyncTask(ctxTest());
$task->begin($trace); // output: bar