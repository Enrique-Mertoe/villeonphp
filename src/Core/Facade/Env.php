<?php

namespace Villeon\Core\Facade;

/**
 * @method static mixed get(string $key, mixed $default = null)
 * @method static array  has(...$keys)
 * @method static array  all()
 */
class Env extends Facade
{
    protected static function getFacadeRef(): string
    {
        return "env";
    }
}
