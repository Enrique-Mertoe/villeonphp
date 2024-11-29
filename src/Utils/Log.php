<?php

namespace Villeon\Utils;

class Log
{
    /**
     * @param string $tag
     * @param string|null $message
     * @return void
     */
    public static function i(string $tag, ?string $message): void
    {
        Console::Warn("$tag \t\t: $message");
    }

    public static function d(string $tag, string $message): void
    {
        Console::Write("$tag \t\t: $message");
    }
}