<?php

namespace Villeon\DB\DataTypes;
class DataTypes
{
    // Static string constants for types
    /**
     * @var string
     */
    public static string $STRING = "STRING";
    /**
     * @var string
     */
    public static string $INT = "INT";
    /**
     * @var string
     */
    public static string $BOOL = "BOOL";

    /**
     * Return a new StringDataType object with additional SQL parameters.
     * @param int|null $length
     * @param bool $binary
     * @param array $options
     * @return StringDataType
     */
    public static function STRING(?int $length = 255, bool $binary = false, array $options = []): StringDataType
    {
        return new StringDataType($length, $binary, $options);
    }

    /**
     * Return a new IntegerDataType object with additional SQL parameters.
     * @param int|null $length
     * @return IntegerDataType
     */
    public static function INTEGER(?int $length = 11): IntegerDataType
    {
        return new IntegerDataType($length, false);
    }
}

