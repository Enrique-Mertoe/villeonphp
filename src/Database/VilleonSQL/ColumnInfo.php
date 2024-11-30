<?php

namespace Villeon\Database\VilleonSQL;

class ColumnInfo
{
    public string $name;
    public string $type;
    public bool $unique;
    public bool $primary;
    public bool $null;
    public mixed $default;

    public function __construct($info)
    {
        $this->name = $info["COLUMN_NAME"] ?? "";
        $this->null = $info['IS_NULLABLE'] === 'YES';
        $this->default = $info['COLUMN_DEFAULT'] ?? "";
        $this->primary = $info['PRIMARY_KEY'] ?? false;
        $this->type = $info["DATA_TYPE"] ?? "int";
    }

    public static function instance(array $info): static
    {
        return new static($info);
    }
}