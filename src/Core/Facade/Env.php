<?php

namespace Villeon\Core\Facade;

use Villeon\Library\Collection\Dict;

/**
 * @method static mixed get(string $key, mixed $default = null)
 * @method static array  has(...$keys)
 * @method static Dict  all()
 */
class Env extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeRef(): string
    {
        return "env";
    }
}
