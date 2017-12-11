<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-12-11
 * Time: 14:40
 */

namespace Inhere\Koa;

use Swoole\Http\Client;

/**
 * Class HttpClient
 * @package Inhere\Koa
 */
class HttpClient extends Client
{
    public function asyncGet($uri)
    {
        return call_cc(function($k) use($uri) {
            $this->get($uri, $k);
        });
    }

    public function asyncPost($uri, $post)
    {
        return call_cc(function($k) use($uri, $post) {
            $this->post($uri, $post, $k);
        });
    }

    public function asyncExecute($uri)
    {
        return call_cc(function($k) use($uri) {
            $this->execute($uri, $k);
        });
    }

    public function awaitGet($uri, $timeout = 1000)
    {
        return race([
            call_cc(function($k) use($uri) {
                $this->get($uri, $k);
            }),
            timeout($timeout),
        ]);
    }
}