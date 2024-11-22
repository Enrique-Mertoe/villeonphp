<?php

namespace Villeon\Database\VilleonSQL\DataTypes;

class IntegerDataType extends AbstractDataType
{
    private ?int $length;

    public function toSql(): string
    {
        return "INT";
    }
}
