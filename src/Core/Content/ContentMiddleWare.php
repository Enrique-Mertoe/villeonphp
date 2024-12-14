<?php

namespace Villeon\Core\Content;

interface ContentMiddleWare
{
    public function beforeRequest(callable $f);

    public function afterRequest(callable $f);
}
