<?php

namespace Villeon\Core\ORM\Models;

use Villeon\Core\Exceptions\UnImplementedException;

/**
 * @template T
 * @method static static[] all()
 * @method static static orderBy(string|array $columns, string $direction = '')
 * @method static static create()
 * @method static static first()
 * @method static static filterBy(array $filters)
 * @method static static where()
 * @method static static query()
 * @method static static limit(...$limit)
 */
class Model
{
    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments)
    {
        return (new ModelBuilder(static::class))->$name(...$arguments);
    }

    /**
     * @throws UnImplementedException
     */
    protected function getAttributes(): array
    {
        throw new UnImplementedException("This method need to be implemented.");
    }
}
