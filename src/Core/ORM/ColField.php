<?php

namespace Villeon\Core\ORM;

use Villeon\Core\ORM\DataType\DataType;

/**
 * Class representing a column field in the ORM (Object-Relational Mapping).
 * This class defines the structure and properties of a field in a database table, such as its data type, default value,
 * and constraints like primary key, unique, nullable, and auto-increment.
 *
 * Supports:
 *  - Primary Keys, Unique Constraints
 *  - Foreign Keys & Indexing
 *  - Column Length, Precision, Scale
 *  - Charset & Collation
 *  - Extra SQL Options
 */
class ColField
{
    /**
     * @var string The name of the column.
     */
    public string $name;

    /**
     * @var DataType|int The data type of the column.
     */
    public DataType|int $type;

    /**
     * @var int|null The length of the column (for VARCHAR, CHAR, etc.).
     */
    public ?int $length = null;

    /**
     * @var int|null Precision for DECIMAL, FLOAT types.
     */
    public ?int $precision = null;

    /**
     * @var int|null Scale for DECIMAL type (number of digits after decimal).
     */
    public ?int $scale = null;

    /**
     * @var bool Whether this column is a primary key.
     */
    public bool $isPrimary = false;

    /**
     * @var bool Whether this column must be unique.
     */
    public bool $isUnique = false;

    /**
     * @var bool Whether this column allows NULL values.
     */
    public bool $allowNull = false;

    /**
     * @var bool Whether this column is auto-incremented.
     */
    public bool $autoValue = false;

    /**
     * @var mixed Default value for the column.
     */
    public mixed $default = null;

    /**
     * @var string[]|null Defines the foreign key relation (`['table' => 'users', 'column' => 'id', 'onDelete' => 'CASCADE']`).
     */
    public ?array $foreignKey = null;

    /**
     * @var string|null Defines the index type (e.g., "INDEX", "FULLTEXT", "UNIQUE").
     */
    public ?string $indexType = null;

    /**
     * @var string|null Check constraint (e.g., `age > 18`).
     */
    public ?string $checkConstraint = null;

    /**
     * @var string|null Character set for text-based columns.
     */
    public ?string $charset = null;

    /**
     * @var string|null Collation for text-based columns.
     */
    public ?string $collation = null;

    /**
     * @var string|null Extra SQL options (e.g., `ON UPDATE CURRENT_TIMESTAMP`).
     */
    public ?string $extraOptions = null;

    /**
     * ColField constructor.
     *
     * @param string $name The name of the column.
     * @param DataType|int $type The data type of the column.
     * @param int|null $length Length for string columns.
     * @param int|null $precision Precision for decimal/numeric types.
     * @param int|null $scale Scale for decimal/numeric types.
     * @param mixed $default Default value.
     * @param bool $isPrimary Primary key flag.
     * @param bool $isUnique Unique constraint flag.
     * @param bool $allowNull NULL constraint flag.
     * @param bool $autoValue Auto-increment flag.
     * @param array|null $foreignKey Foreign key definition.
     * @param string|null $indexType Index type (INDEX, FULLTEXT, etc.).
     * @param string|null $checkConstraint SQL CHECK constraint.
     * @param string|null $charset Character set for text-based columns.
     * @param string|null $collation Collation for text-based columns.
     * @param string|null $extraOptions Additional SQL options.
     */
    public function __construct(
        string       $name,
        DataType|int $type,
        ?int         $length = null,
        ?int         $precision = null,
        ?int         $scale = null,
        mixed        $default = null,
        bool         $isPrimary = false,
        bool         $isUnique = false,
        bool         $allowNull = false,
        bool         $autoValue = false,
        ?array       $foreignKey = null,
        ?string      $indexType = null,
        ?string      $checkConstraint = null,
        ?string      $charset = null,
        ?string      $collation = null,
        ?string      $extraOptions = null
    )
    {
        $this->name = $name;
        $this->type = $type;
        $this->length = $length;
        $this->precision = $precision;
        $this->scale = $scale;
        $this->default = $default;
        $this->isPrimary = $isPrimary;
        $this->isUnique = $isUnique;
        $this->allowNull = $allowNull;
        $this->autoValue = $autoValue;
        $this->foreignKey = $foreignKey;
        $this->indexType = $indexType;
        $this->checkConstraint = $checkConstraint;
        $this->charset = $charset;
        $this->collation = $collation;
        $this->extraOptions = $extraOptions;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setType(int|DataType $type): void
    {
        $this->type = $type;
    }

    public function setLength(?int $length): void
    {
        $this->length = $length;
    }

    public function setPrecision(?int $precision): void
    {
        $this->precision = $precision;
    }

    public function setScale(?int $scale): void
    {
        $this->scale = $scale;
    }

    public function setIsPrimary(bool $isPrimary): void
    {
        $this->isPrimary = $isPrimary;
    }

    public function setIsUnique(bool $isUnique): void
    {
        $this->isUnique = $isUnique;
    }

    public function setAllowNull(bool $allowNull): void
    {
        $this->allowNull = $allowNull;
    }

    public function setAutoValue(bool $autoValue): void
    {
        $this->autoValue = $autoValue;
    }

    public function setDefault(mixed $default): void
    {
        $this->default = $default;
    }

    public function setForeignKey(?array $foreignKey): void
    {
        $this->foreignKey = $foreignKey;
    }

    public function setIndexType(?string $indexType): void
    {
        $this->indexType = $indexType;
    }

    public function setCheckConstraint(?string $checkConstraint): void
    {
        $this->checkConstraint = $checkConstraint;
    }

    public function setCharset(?string $charset): void
    {
        $this->charset = $charset;
    }

    public function setCollation(?string $collation): void
    {
        $this->collation = $collation;
    }

    public function setExtraOptions(?string $extraOptions): void
    {
        $this->extraOptions = $extraOptions;
    }

}
