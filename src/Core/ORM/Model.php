<?php

namespace Villeon\Core\ORM;

use Villeon\Core\ORM\Models\ModelFactory;
use Villeon\Core\ORM\Models\ModelObj;

abstract class Model
{
    abstract public function schema(FieldSchema $table): void;

    public static function obj(): ModelObj
    {
        return new ModelObj(new ModelFactory(static::class));
    }

    public function __call(string $name, array $arguments)
    {
        self::obj()->$name($this, ...$arguments);
    }
}
