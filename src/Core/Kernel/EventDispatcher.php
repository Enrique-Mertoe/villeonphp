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
     * @return void
     */
    public function onSuccess(string $content): void;

    /**
     * @param int $code
     * @param string $content
     * @return void
     */
    public function onFail(int $code, string $content): void;

    /**
     * @param Response $response
     * @return int
     */
    public function onResponse(Response $response):int;
}
