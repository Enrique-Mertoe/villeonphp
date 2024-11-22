<?php

namespace Villeon\Core\Facades;

use RuntimeException;
use Villeon\Utils\Collection;

abstract class Facade
{
    /**
     * @var array<string,mixed>
     */
    protected static array $instances = [];


    /**
     * @param $method
     * @param $args
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        return static::getFacadeRoot(static::accessor(), $method, $args);
    }

    /**
     * @param $name
     * @param $method
     * @param $args
     * @return mixed
     */
    protected static function getFacadeRoot($name, $method, $args): mixed
    {

        if (isset(self::$instances[$name])) {
            $instance = self::$instances[$name];
            return $instance->$method(...$args);
        }
        throw new RuntimeException("A facade name: $name has not been set.");
    }

    protected static function accessor(): string
    {
        throw new RuntimeException('This method must be Implemented!!');
    }

    public static function setInstance(string $name, mixed $instance): void
    {
        self::$instances[$name] = $instance;
    }

    public static function getFacade(string $name): mixed
    {
        return isset(self::$instances[$name]) ? static::$instances[$name] : null;
    }

}
