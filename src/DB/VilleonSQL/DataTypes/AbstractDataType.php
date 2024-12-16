<?php

namespace Villeon\DB\VilleonSQL\DataTypes;

abstract class AbstractDataType
{
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

    /**
     * @return string
     */
    abstract public function toSql(): string;

    /**
     * @param $value
     * @param array $options
     * @return string
     */
    public function stringify($value, array $options = []): string
    {
        return (string)$value;
    }

    /**
     * @param array $options
     * @return string
     */
    public function toString(array $options = []): string
    {
        return $this->toSql();
    }
}
