<?php

namespace Villeon\Core\ORM;

use Villeon\Core\ORM\DataTypes\DataType;
use Villeon\Core\useVilleon\Core\ORM\ColField;

class DBSchema
{
    private string $table;
    private array $fields = [];

    public function string(
        string $name,
        int    $length = null,
        string $default = null,
        bool   $primary = false,
        bool   $unique = false,
        bool   $null = false): ColField
    {
        $col = new ColField($name, $length ? DataType::STRING : DataType::STRING($length),
            default: $default, isPrimary: $primary,
            isUnique: $unique, allowNull: $null);
        $this->fields[$name] = $col;
        return $col;
    }

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

    public function bool(
        string $name,
        bool   $default = null,
        bool   $null = false): ColField
    {
        $col = new ColField($name,
            DataType::BOOL,
            default: $default,
            allowNull: $null,);
        $this->fields[$name] = $col;
        return $col;
    }

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
    public function date(string $name):ColField
    {
        $col = new ColField($name,
            DataType::DATE);
        $this->fields[$name] = $col;
        return $col;
    }

    public function table(?string $name = null): ?string
    {
        if ($name === null) {
            return $this->table;
        }
        $this->table = $name;
        return null;
    }


}
