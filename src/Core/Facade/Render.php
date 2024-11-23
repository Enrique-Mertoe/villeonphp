<?php

namespace Villeon\Core\Facade;

/**
 * @method static Render template (string $name, array $context = [])
 * @method static Render json (array $context)
 */
class Render extends Facade
{
    protected static function accessor(): string
    {
        return "render";
    }
}