<?php

namespace Villeon\Manager\Handlers;

use Villeon\Core\ORM\Connectors\SQLConnector;
use Villeon\Core\ORM\FieldSchema;
use Villeon\Core\ORM\Schema;

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

    public function info(): ModelDef
    {
        $name = class_basename($this->model);
        $schema = new FieldSchema();
        $schema->table(strtolower($name) . "s");
        $inst = new $this->model;
        $inst->schema($schema);
        return new ModelDef($name, $schema,Schema::exits($schema->table()));
    }
}
