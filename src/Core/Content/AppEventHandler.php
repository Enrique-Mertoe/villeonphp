<?php

namespace Villeon\Core\Content;

use Villeon\Http\Response;

interface AppEventHandler
{
    public function onResponse(Response $response);

    public function onError(Response $response);
}
