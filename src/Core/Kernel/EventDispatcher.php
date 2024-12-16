<?php

namespace Villeon\Core\Kernel;

use Villeon\Http\Response;

/**
 *
 */
interface EventDispatcher
{
    /**
     * @param string $content
     * @return mixed
     */
    public function onSuccess(string $content);

    /**
     * @param int $code
     * @param string $content
     * @return mixed
     */
    public function onFail(int $code, string $content);

    /**
     * @param Response $response
     * @return int
     */
    public function onResponse(Response $response):int;
}
