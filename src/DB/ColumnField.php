<?php

namespace Villeon\DB;

class ColumnField
{
    public function __construct(
        string $name,
        DataType $type,
        bool $isPrimary,
        bool $isUnique,
        bool $allowNull
    )
    {
    }
}
