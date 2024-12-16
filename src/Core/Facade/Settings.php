<?php

namespace Villeon\Core\Facade;

/**
 * @brief Settings Facade
 * @method static array get(string $key, mixed $default = null)
 * @method static set(string $key, mixed $value)
 * @method static array all()
 */
class Settings extends Facade
{
    protected static function getFacadeRef(): string
    {
        return "settings";
    }
}
