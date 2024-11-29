<?php

namespace Villeon\Core\Facade;

/**
 * @method static mixed get(string $key, mixed $default = null)
 * @method static array  has(...$keys)
 */
class Env extends Facade
{
    protected static function accessor(): string
    {
        return "env";
    }
}