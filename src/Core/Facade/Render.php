<?php

namespace Villeon\Core\Facade;

use Villeon\Http\Response;

/**
 * @method static string template (string $name, array $context = [])
 * @method static Response json (array $context)
 */
class Render extends Facade
{
    protected static function getFacadeRef(): string
    {
        return "render";
    }
}
