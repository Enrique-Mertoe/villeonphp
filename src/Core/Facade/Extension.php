<?php

namespace Villeon\Core\Facade;

use Villeon\Support\Extensions\ExtensionBuilder;

/**
 * @method static Extension add (string $extension_name, ExtensionBuilder $builder, bool $enabled = true)
 * @method static Extension enabled (string $extension_name, bool $enabled)
 * @method static Extension get (string $extension_name)
 */
class Extension extends Facade
{
    protected static function accessor(): string
    {
        return "extension";
    }
}