<?php

namespace Villeon\Core\ORM\DataType;

class IntDataType extends DataType
{
    private ?int $length;

    public function __construct(?int $length = 255)
    {
        parent::__construct('BIGINT', '');
        $this->length = $length;
    }

    public function toSql(mixed $default = null): string
    {
        return "BIGINT( $this->length)" . ($default !== null) ? " DEFAULT $default" : "";
    }
}
