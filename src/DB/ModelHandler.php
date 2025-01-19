<?php

namespace Villeon\DB;

use RuntimeException;
use Villeon\Core\ORM\Connectors\SQLConnector;
use Villeon\DB\VilleonSQL\DataTypes\AbstractDataType;
use Villeon\Manager\Manager;

class ModelHandler
{
    private string $name;
    private ?string $alias;
    /**
     * @var ModelColumn[]
     */
    private ?array $attributes;

    public function __construct($name, ?string $alias, ?array $attributes)
    {
        $this->name = strtolower($name);
        $this->alias = $alias;
        $this->attributes = $attributes;
    }

    /**
     * @param $name
     * @param string|null $alias
     * @param ModelColumn[] $attributes
     * @return ModelHandler
     */
    public static function define($name, ?string $alias, ?array $attributes): ModelHandler
    {
        return new static($name, $alias, $attributes);
    }

    public function create(): bool|array
    {
        $sql = self::getAttributes($this->attributes ?: []);
        print_r($sql);
        Manager::createModel($this->name, $this->alias, $this->attributes);
        return true;
    }

    public function getAttributes(array $attributes): string
    {

        $tableName = $this->alias ?: $this->name ?? throw new RuntimeException('Table name not defined.');

        $columns = $attributes;
        $sql = "CREATE " . "TABLE IF NOT EXISTS `$tableName` (\n";

        $columnDefinitions = [];

        foreach ($columns as $definition) {
            $name = $definition["name"];
            $columnDef = "`$name` ";
            $type = $definition['type'] ?? throw new RuntimeException("Type not defined for column `$name`.");
            if ($type instanceof AbstractDataType) {
                $columnDef .= $type->toSql();
            } elseif (is_string($type)) {
                $columnDef .= $type;
            } else {
                throw new RuntimeException("Invalid type for column `$name`.");
            }


            $columnDef .= ($definition['nullable'] == "true") ? " NULL" : " NOT NULL";


            if (!empty($definition['primary']) && $definition['primary'] != "false")
                $columnDef .= " PRIMARY KEY";


            if (isset($definition['unique']) && $definition['unique'] == "true")
                $columnDef .= " UNIQUE";

            if (isset($definition['default']))
                $columnDef .= " DEFAULT " . (is_bool($definition['default']) ? ($definition['default'] ? '1' : '0') : $definition['default']);

            if (isset($definition['auto']) && $definition['auto'] == "true")
                $columnDef .= " AUTO_INCREMENT";


            $columnDefinitions[] = $columnDef;
        }
        $sql .= implode(",\n", $columnDefinitions) . "\n);";
        return $sql;
    }
}
