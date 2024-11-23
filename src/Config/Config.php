<?php

namespace Villeon\Config;

use Villeon\Core\Facade\Facade;

/**
 * @method static Config load_module(string $name)
 * @method static Config set_src(string $dir_name)
 * @see ConfigBuilder
 */
class Config extends Facade
{
    protected static function accessor(): string
    {
        return "config";
    }
}