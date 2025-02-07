<?php

namespace Villeon\Core\ORM;

use AllowDynamicProperties;
use Villeon\Core\ORM\Models\ModelFactory;
use Villeon\Core\ORM\Models\ModelObj;

#[AllowDynamicProperties]
abstract class Model
{
    abstract public function schema(FieldSchema $table): void;

    public static function obj(): ModelObj
    {
        return new ModelObj(new ModelFactory(static::class));
    }

    public function __call(string $name, array $arguments)
    {
        return self::obj()->$name($this, ...$arguments);
    }
}
