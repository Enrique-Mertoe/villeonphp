<?php

namespace Villeon\Core\ORM\DataType;

abstract class DataType
{
    /**
     * @var string Represents a generic string data type.
     */
    public const STRING = 101;

    /**
     * @var string Represents a generic integer data type.
     */
    public const INT = 102;

    /**
     * @var string Represents a generic boolean data type.
     */
    public const BOOL = 103;
    public const DATE = 104;

    /**
     * @var string
     */
    protected string $key;

    /**
     * @var string
     */
    protected string $dialectTypes;

    /**
     * @param string $key
     * @param string $dialectTypes
     */
    public function __construct(string $key, string $dialectTypes)
    {
        $this->key = $key;
        $this->dialectTypes = $dialectTypes;
    }

    abstract public function toSql(mixed $default = null): string;

    /**
     * @param array $options
     * @return string
     */
    public function toString(array $options = []): string
    {
        return $this->toSql();
    }

    /**
     * Generate a StringDataType object for defining string columns in the database.
     *
     * @param int|null $length The maximum length of the string (default: 255).
     * @param bool $binary Whether the string should be stored as binary data (default: false).
     * @param array $options Additional SQL parameters for customization (default: empty array).
     *
     * @return StrDataType An object representing the string data type.
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
    ): StrDataType
    {
        return new StrDataType($length, $binary, $options);
    }

    public static function ENUM($values): EnumDataType
    {
        return new EnumDataType($values);
    }

    public static function TIME(bool $defaultCurrent = false, bool $onUpdateCurrent = false): TimestampDataType
    {
        return new TimestampDataType($onUpdateCurrent);
    }

    public static function DATETIME($default = null): DateTimeDataType
    {
        return new DateTimeDataType($default);
    }


    /**
     * Generate an IntegerDataType object for defining integer columns in the database.
     *
     * @param int|null $length The maximum display width of the integer (default: 11).
     *
     * @return IntDataType An object representing the integer data type.
     *
     * Example:
     * ```php
     * $integerColumn = DataTypes::INTEGER(10);
     * ```
     */
    public static function INT(?int $length = 11): IntDataType
    {
        return new IntDataType($length, false);
    }

    public function getKey(): string
    {
        return $this->key;
    }



}
