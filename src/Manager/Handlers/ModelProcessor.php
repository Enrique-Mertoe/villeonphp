<?php

namespace Villeon\Manager\Handlers;

use Villeon\Core\ORM\DBSchema;

class ModelProcessor
{
    private string $model;

    public function __construct(string $model)
    {
        $this->model = $model;
    }

    public static function of(string $model): static
    {
        return new static($model);
    }

    public function process(): ModelDef
    {
        $name = class_basename($this->model);
        $schema = new DBSchema();
        $schema->table(strtolower($name) . "s");
        $inst = new $this->model;
        $inst->schema($schema);
        return new ModelDef($name, $schema);
    }
}
