<?php

namespace Villeon\Core\Routing;

use Villeon\Core\Facades\Facade;

/**
 * @method static Route get (string $rule, callable $controller)
 * @method static Route post (string $rule, callable $controller)
 * @method static Route put (string $rule, callable $controller)
 * @method static Route delete (string $rule, callable $controller)
 * @method static Route route (string $rule, ...$options)
 * @method static Route error (int $code, callable $controller)
 * @see Router
 */
class Route extends Facade
{
    protected static function accessor(): string
    {
        return "route";
    }
}