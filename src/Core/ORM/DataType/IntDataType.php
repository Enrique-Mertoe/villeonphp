<?php

namespace Villeon\Core\ORM\DataTypes;

class IntDataType extends DataType
{
    private ?int $length;

    public function __construct(?int $length = 255)
    {
        parent::__construct('BIGINT', '');
        $this->length = $length;
    }

    public function toSql(): string
    {
        return "BIGINT( $this->length)";
    }
}
