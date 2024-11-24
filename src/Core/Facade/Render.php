<?php

namespace Villeon\Core\Facade;

/**
 * @method static Extension template (string $name, array $context = [])
 * @method static Extension json (array $context)
 */
class Render extends Facade
{
    protected static function accessor(): string
    {
        return "render";
    }
}