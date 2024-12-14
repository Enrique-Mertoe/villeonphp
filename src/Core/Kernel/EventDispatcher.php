<?php

namespace Villeon\Core\Kernel;

use Villeon\Http\Response;

interface EventDispatcher
{
    public function onSuccess(string $content);

    public function onFail(int $code, string $content);
    public function onResponse(Response $response):int;
}
