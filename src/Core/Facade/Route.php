<?php

namespace Villeon\Core\Facade;

/**
 * @method static \Villeon\Core\Routing\Route get (string $rule, callable $controller):Router
 * @method static \Villeon\Core\Routing\Route post (string $rule, callable $controller)
 * @method static \Villeon\Core\Routing\Route put (string $rule, callable $controller)
 * @method static \Villeon\Core\Routing\Route delete (string $rule, callable $controller)
 * @method static \Villeon\Core\Routing\Route route (string $rule, ...$options)
 * @method static \Villeon\Core\Routing\Route error_handler (int $code, callable $controller)
 * @see Router
 */
class Route extends Facade
{
    protected static function accessor(): string
    {
        return "route";
    }
}