<?php

namespace Villeon\Core;

session_start();

class Session
{
    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function pop(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        if (self::has($key))
            return $_SESSION[$key];
        return $default;
    }

}
