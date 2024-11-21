<?php

namespace SMVTemplating\Filter;

class SMVTemplateFilter
{
    public static function handle(string $filterName, $value)
    {
        $methodName = 'filter' . ucfirst($filterName);
        if (method_exists(self::class, $methodName)) {
            return self::$methodName($value);
        }
        return $value;
    }

    private static function upper($value): string
    {
        return strtoupper($value);
    }

    private static function trim($value): string
    {
        return trim($value);
    }

    private static function encode($value): bool|string
    {
        return json_encode($value);
    }
}
