<?php

namespace Villeon\Core\ORM\Models;

use Villeon\Core\ORM\OrderMode;

/**
 * @template T
 * @method create(array $data)
 * @method ?object find($key)
 * @method ModelObj filter($col, $operator = null, $value = null)
 * @method ModelObj or ($col, $operator = null, $value = null)
 * @method ModelObj and ($col, $operator = null, $value = null)
 * @method ModelObj orderBy(array|string $columns, OrderMode $direction = null)
 * @method ModelObj limit(int $offset, int $stop = null)
 * @method first()
 * @method update(array|object $info)
 * @method find(mixed $key)
 * @method array all()
 *
 */
class ModelObj
{
    private mixed $builder;

    public function __construct($builder)
    {
        $this->builder = $builder;
    }

    public function __call(string $name, array $arguments)
    {
        return $this->builder->$name(...$arguments);
    }
}
