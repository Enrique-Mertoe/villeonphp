<?php

namespace Villeon\Core\Content;

use Villeon\Http\Response;

interface AppEventHandler
{
    function onResponse(Response $response);
}
