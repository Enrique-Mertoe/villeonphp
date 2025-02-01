<?php

namespace Villeon\Manager\Handlers;

use Villeon\Core\ORM\DBSchema;

class ModelDef
{
    public string $name;
    public DBSchema $schema;

    public function __construct(string $name, DBSchema $schema)
    {
        $this->name = $name;
        $this->schema = $schema;
    }
}
