<?php

namespace Villeon\Core\ORM\DataType;


class StrDataType extends DataType
{
    private ?int $length;

    /**
     * @param int|null $length
     */
    public function __construct(?int $length = 255)
    {
        parent::__construct('STRING', 'varchar');
        $this->length = $length;
    }

    /**
     * @return string
     */
    public function toSql(): string
    {
        return "VARCHAR(" . $this->length . ")";
    }
}
