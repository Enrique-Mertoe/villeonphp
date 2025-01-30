<?php

namespace Villeon\Core\useVilleon\Core\ORM;

use Villeon\Core\ORM\DataTypes\DataType;
use Villeon\Core\ORM\RelationShip;

/**
 * Class representing a column field in the ORM (Object-Relational Mapping).
 * This class defines the structure and properties of a field in a database table, such as its data type, default value,
 * and constraints like primary key, unique, nullable, and auto-increment.
 * @method RelationShip foreign()
 */
class ColField
{
    /**
     * @var int|DataType The data type of the column field, which can either be an integer or an instance of DataType.
     *                   This determines the type of values the column will hold.
     */
    public int|DataType $type;

    /**
     * @var bool Whether the column field is a primary key. Defaults to false.
     *           If true, this field uniquely identifies a record in the table.
     */
    public bool $isPrimary;

    /**
     * @var bool Whether the column field is unique. Defaults to false.
     *           If true, the values in this column must be unique across all rows.
     */
    public bool $isUnique;

    /**
     * @var bool Whether the column field allows NULL values. Defaults to false.
     *           If true, the column can have NULL values.
     */
    public bool $allowNull;

    /**
     * @var bool Whether the column field has an auto-generated value (e.g., auto-increment for integers). Defaults to false.
     *           If true, the field's value will be automatically generated (commonly used for primary keys).
     */
    public bool $autoValue;

    /**
     * @var mixed The default value for the column field. Defaults to null.
     *            This is the value that will be used if no value is provided for this field when a record is created.
     */
    public mixed $default;

    /**
     * ColField constructor.
     *
     * @param DataType|int $type The data type of the column field. This can be either an integer or an instance of DataType.
     * @param mixed $default The default value for the column. Defaults to null if not provided.
     * @param bool $isPrimary Whether the column is a primary key. Defaults to false.
     * @param bool $isUnique Whether the column is unique. Defaults to false.
     * @param bool $allowNull Whether the column allows NULL values. Defaults to false.
     * @param bool $autoValue Whether the column value is auto-generated (e.g., auto-increment). Defaults to false.
     */
    public function __construct(
        string $name,
        DataType|int $type,
        mixed $default = null,
        bool $isPrimary = false,
        bool $isUnique = false,
        bool $allowNull = false,
        bool $autoValue = false
    )
    {
        $this->type = $type;
        $this->isPrimary = $isPrimary;
        $this->isUnique = $isUnique;
        $this->allowNull = $allowNull;
        $this->default = $default;
        $this->autoValue = $autoValue;
    }
}
