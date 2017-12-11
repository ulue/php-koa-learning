<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-12-08
 * Time: 17:04
 */

namespace Inhere\Koa;

/**
 * Class GenWrapper
 * @package Inhere\Koa
 */
class GenWrapper
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
    public function throw(\Exception $e)
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