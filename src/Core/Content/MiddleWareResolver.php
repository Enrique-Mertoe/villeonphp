<?php

namespace Villeon\Core\Content;

interface MiddleWareResolver
{
    public function onBeforeRequest(\Closure $f);

    public function onAfterRequest(\Closure $f);
}
