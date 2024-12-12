<?php

namespace Villeon\Utils;

/**
 *
 */
class Log
{
    /**
     * @param string $tag
     * @param string|null $message
     * @return void
     */
    public static function i(string $tag, ?string $message): void
    {
        Console::Info("$tag \t\t: $message");
    }

    /**
     * @param string $tag
     * @param string|null $message
     * @return void
     */
    public static function e(string $tag, ?string $message): void
    {
        Console::Error("$tag \t\t: $message");
    }

    /**
     * @param string $tag
     * @param string|null $message
     * @return void
     */
    public static function w(string $tag, ?string $message): void
    {
        Console::Warn("$tag \t\t: $message");
    }

    /**
     * @param string $tag
     * @param string $message
     * @return void
     */
    public static function d(string $tag, string $message): void
    {
        Console::Write("$tag \t\t: $message");
    }
}
