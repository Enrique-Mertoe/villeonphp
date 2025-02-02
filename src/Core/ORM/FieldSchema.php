<?php

namespace Villeon\Core\ORM;

use Villeon\Core\ORM\DataType\DataType;
use Villeon\Core\ORM\ColField;

/**
 * FieldSchema Class
 *
 * This class provides a schema builder for defining database tables, columns,
 * and their attributes. It allows the creation of various column types (e.g.,
 * string, integer, boolean) and manages their properties, such as whether
 * they are primary keys, unique, or nullable.
 *
 * @package    Villeon\Core\ORM
 * @author     Your Name <abutimartin778@gmail.com>
 * @copyright  2024 Villeon
 * @license    MIT License
 * @version    1.0.0
 * @link       https://github.com/YourUsername/villeonphp
 */
class FieldSchema
{
    /**
     * The name of the table.
     *
     * @var string
     */
    public string $table;

    /**
     * Array holding the fields (columns) defined for the table.
     *
     * @var ColField[]
     */
    public array $fields = [];

    /**
     * Defines a string column for the table.
     *
     * @param string $name The name of the column.
     * @param int|null $length The length of the string (optional).
     * @param string|null $default The default value for the column (optional).
     * @param bool $primary Whether the column is a primary key (default is false).
     * @param bool $unique Whether the column is unique (default is false).
     * @param bool $null Whether the column allows null values (default is false).
     *
     * @return ColField The column field definition.
     */
    public function string(
        string $name,
        int    $length = null,
        string $default = null,
        bool   $primary = false,
        bool   $unique = false,
        bool   $null = false): ColField
    {
        $col = new ColField($name, $length ? DataType::STRING($length) : DataType::STRING,
            default: $default, isPrimary: $primary,
            isUnique: $unique, allowNull: $null);
        $this->fields[$name] = $col;
        return $col;
    }

    /**
     * Defines an integer column for the table.
     *
     * @param string $name The name of the column.
     * @param bool $unsigned Whether the column is unsigned (default is false).
     * @param int|null $default The default value for the column (optional).
     * @param bool $primary Whether the column is a primary key (default is false).
     * @param bool $unique Whether the column is unique (default is false).
     * @param bool $null Whether the column allows null values (default is false).
     * @param bool $auto Whether the column has an auto-increment feature (default is false).
     *
     * @return ColField The column field definition.
     */
    public function int(
        string $name, bool $unsigned = false,
        int    $default = null,
        bool   $primary = false,
        bool   $unique = false,
        bool   $null = false,
        bool   $auto = false): ColField
    {
        $col = new ColField($name,
            DataType::INT,
            default: $default, isPrimary: $primary,
            isUnique: $unique, allowNull: $null,
            autoValue: $auto);
        $this->fields[$name] = $col;
        return $col;
    }

    /**
     * Defines a boolean column for the table.
     *
     * @param string $name The name of the column.
     * @param bool|null $default The default value for the column (optional).
     * @param bool $null Whether the column allows null values (default is false).
     *
     * @return ColField The column field definition.
     */
    public function bool(
        string $name,
        bool   $default = null,
        bool   $null = false): ColField
    {
        $col = new ColField($name,
            DataType::BOOL,
            default: $default,
            allowNull: $null);
        $this->fields[$name] = $col;
        return $col;
    }

    /**
     * Defines an ID column for the table, typically used as a primary key.
     *
     * @param string $name The name of the column (default is 'id').
     * @param int|null $default The default value for the column (optional).
     * @param bool $primary Whether the column is a primary key (default is true).
     * @param bool $auto Whether the column has an auto-increment feature (default is true).
     *
     * @return ColField The column field definition.
     */
    public function id(
        string $name = "id",
        int    $default = null,
        bool   $primary = true,
        bool   $auto = true): ColField
    {
        $col = new ColField($name,
            DataType::INT,
            default: $default, isPrimary: $primary,
            autoValue: $auto);
        $this->fields[$name] = $col;
        return $col;
    }

    /**
     * Defines a date column for the table.
     *
     * @param string $name The name of the column.
     *
     * @return ColField The column field definition.
     */
    public function date(string $name, $default = null, bool $null = false): ColField
    {
        $col = new ColField($name,
            DataType::DATE, default: $default, allowNull: $null);
        $this->fields[$name] = $col;
        return $col;
    }

    public function enum(string $name, array $values, string $default = null, bool $null = false): ColField
    {
        $col = new ColField($name, DataType::ENUM($values), $default, allowNull: $null);
        return $this->fields[$name] = $col;
    }

    public function time(string $name, bool $defaultCurrent = false, bool $onUpdateCurrent = false): ColField
    {
        $col = new ColField($name, DataType::TIME($defaultCurrent, $onUpdateCurrent));
        return $this->fields[$name] = $col;
    }

    public function datetime(string $name, bool $null = false, $default = 'CURRENT_TIMESTAMP'): ColField
    {
        $col = new ColField($name, DataType::DATETIME($default), allowNull: $null);
        return $this->fields[$name] = $col;
    }

    /**
     * Defines or retrieves the table name.
     *
     * If a name is provided, it sets the table name. If no name is provided,
     * it retrieves the current table name.
     *
     * @param string|null $name The name of the table (optional).
     *
     * @return string|null The table name or null if it's being set.
     */
    public function table(?string $name = null): ?string
    {
        if ($name === null) {
            return $this->table;
        }
        $this->table = $name;
        return null;
    }
}
