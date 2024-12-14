<?php

namespace Villeon\Core\Facade;

use RuntimeException;
use Villeon\Library\Collection\Dict;
use Villeon\Utils\Collection;

/**
 *
 */
abstract class Facade
{
    /**
     * @var Dict $facades
     */
    private static Dict $facades;
    /**
     * @param $method
     * @param $args
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        $name = static::getFacadeRef();
        if ($facade = self::$facades[$name]) {
            return $facade->$method(...$args);
        }
        throw new RuntimeException("A facade name: $name has not been set.");
    }

    /**
     * @return string
     */
    protected static function getFacadeRef(): string
    {
        throw new RuntimeException('This method must be Implemented!!');
    }

    /**
     * @param string $name
     * @param mixed $instance
     * @return void
     */
    public static function setFacade(string $name, mixed $instance): void
    {
        if (!isset(self::$facades))
            self::$facades = \dict();
        self::$facades[$name] = $instance;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public static function getFacade(string $name): mixed
    {
        return self::$facades->get($name);
    }

}
