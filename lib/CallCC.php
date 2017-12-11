<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-12-11
 * Time: 14:37
 */

namespace Inhere\Koa;

/**
 * Class CallCC - call-with-current-continuation
 * @package Inhere\Koa
 */
class CallCC implements AsyncInterface
{
    /** @var callable */
    public $fun;

    public function __construct(callable $fun)
    {
        $this->fun = $fun;
    }

    public function begin(callable $continuation)
    {
        $fun = $this->fun;
        $fun($continuation);
    }
}