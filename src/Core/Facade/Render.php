<?php

namespace Villeon\Core\Facade;

/**
 * @method static string template (string $name, array $context = [])
 * @method static Extension json (array $context)
 */
class Render extends Facade
{
    protected static function accessor(): string
    {
        return "render";
    }
}