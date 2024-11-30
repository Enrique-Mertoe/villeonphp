<?php

namespace Villeon\Core\Facade;

/**
 * @brief Settings Facade
 * @method static \Villeon\Core\Internal\Settings get(string $key, mixed $default = null)
 * @method static \Villeon\Core\Internal\Settings set(string $key, mixed $value)
 * @method static array all()
 */
class Settings extends Facade
{
    protected static function accessor(): string
    {
        return "settings";
    }
}