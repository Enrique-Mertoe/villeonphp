<?php

namespace Villeon\Database\VilleonSQL\DataTypes;

/**
 * Class DataTypes
 *
 * This class provides static methods and properties to define database column types
 * and generate corresponding objects with optional configurations.
 *
 * It acts as a factory for creating specific data type objects with additional
 * parameters (e.g., length, binary, options) that can be used to define database schemas.
 */
class DataTypes
{
    /**
     * @var string Represents a generic string data type.
     */
    public const STRING = "STRING";

    /**
     * @var string Represents a generic integer data type.
     */
    public const INT = "INT";

    /**
     * @var string Represents a generic boolean data type.
     */
    public const BOOL = "BOOL";

    /**
     * Generate a StringDataType object for defining string columns in the database.
     *
     * @param int|null $length The maximum length of the string (default: 255).
     * @param bool $binary Whether the string should be stored as binary data (default: false).
     * @param array $options Additional SQL parameters for customization (default: empty array).
     *
     * @return StringDataType An object representing the string data type.
     *
     * Example:
     * ```php
     * $stringColumn = DataTypes::STRING(100, false, ['charset' => 'utf8']);
     * ```
     */
    public static function STRING(
        ?int  $length = 255,
        bool  $binary = false,
        array $options = []
    ): StringDataType
    {
        return new StringDataType($length, $binary, $options);
    }

    /**
     * Generate an IntegerDataType object for defining integer columns in the database.
     *
     * @param int|null $length The maximum display width of the integer (default: 11).
     *
     * @return IntegerDataType An object representing the integer data type.
     *
     * Example:
     * ```php
     * $integerColumn = DataTypes::INTEGER(10);
     * ```
     */
    public static function INTEGER(?int $length = 11): IntegerDataType
    {
        return new IntegerDataType($length, false);
    }
}
