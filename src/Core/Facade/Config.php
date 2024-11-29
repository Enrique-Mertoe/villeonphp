<?php

namespace Villeon\Core\Facade;

/**
 * @method static Config load_module(string $name)
 * @method static Config set_src(string $dir_name)
 * @method static array db_info()
 * @see ConfigBuilder
 */
class Config extends Facade
{
    protected static function accessor(): string
    {
        return "config";
    }
}