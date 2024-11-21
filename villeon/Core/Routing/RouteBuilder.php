<?php

namespace Villeon\Core\Routing;
interface RouteBuilder
{
    static function route($rule, ...$options);

    static function post($rule, $controller);

    static function get($rule, $controller);

}
