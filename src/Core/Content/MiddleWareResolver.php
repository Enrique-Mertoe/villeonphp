<?php

namespace Villeon\Core\Content;

use Villeon\Http\Response;

interface MiddleWareResolver
{
    public function onBeforeRequest(\Closure $f);

    public function onAfterRequest(Response $response, bool $ignore = false, \Closure $f = null);
}
