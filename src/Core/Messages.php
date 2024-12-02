<?php

namespace Villeon\Core;


use Villeon\Utils\Log;

class Messages
{
    public const int MESSAGE = 0;
    public const int ERROR = 1;
    public const int INFO = 2;
    public const int WARNING = 3;
    private static array $messages=[];

    public static function add(string $message, int $category = self::MESSAGE): void
    {
        $categories = ["message", "error", "info", "warning"];
        self::$messages[$categories[$category]][] = $message;
    }

    public static function all(bool $with_categories = false, array $category_filter = []): array
    {
        $flashes = self::$messages;
        if (!$with_categories)
            $flashes = array_merge(...array_values($flashes));
        return $flashes;
    }
}