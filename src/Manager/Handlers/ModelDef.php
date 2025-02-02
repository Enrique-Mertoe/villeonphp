<?php

namespace Villeon\Manager\Handlers;

use Villeon\Core\ORM\ColField;
use Villeon\Core\ORM\DataType\DataType;
use Villeon\Core\ORM\FieldSchema;

class ModelDef
{
    public string $name;
    public FieldSchema $schema;
    public bool $exits;

    public function __construct(string $name, FieldSchema $schema, bool $exits)
    {
        $this->name = $name;
        $this->schema = $schema;
        $this->exits = $exits;
    }

    public function mini(): array
    {
        return array_map(function (ColField $field) {
            return [
                "colName" => $field->name,
                "dataType" => $this->getType($field->type),
                "primary" => $field->isPrimary,
                "unique" => $field->isUnique,
                "auto" => $field->autoValue,
                "nullable" => $field->allowNull,
            ];
        }, $this->schema->fields);
    }

    private function getType(DataType|int|string $type): string
    {
        if ($type instanceof DataType) {
            $type = $type->getKey();
        }
        return match ($type) {
            DataType::INT => "INT",
            DataType::STRING => "TEXT",
            DataType::BOOL => "BOOLEAN",
            DataType::DATE => "DATE",
            "STRING" => "VARCHAR",
            default => throw new \InvalidArgumentException("invalid datatype")
        };

    }
}
