<?php

namespace Villeon\DB;

use Villeon\Core\ORM\Connectors\SQLConnector;
use Villeon\DB\VilleonSQL\DataTypes\AbstractDataType;

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
        return SQLConnector::of(SQLConnector::MYSQL)->execute($sql);
    }

    public function getAttributes(array $attributes): string
    {

        $tableName = $this->alias ?: $this->name ?? throw new \RuntimeException('Table name not defined.');

        $columns = $attributes;
        unset($columns['__table_name__']);

        $sql = "CREATE " . "TABLE IF NOT EXISTS `$tableName` (\n";

        $columnDefinitions = [];

        foreach ($columns as $name => $definition) {
            $columnDef = "`$name` ";

            $type = $definition['type'] ?? throw new \RuntimeException("Type not defined for column `$name`.");
            if ($type instanceof AbstractDataType) {
                $columnDef .= $type->toSql();
            } elseif (is_string($type)) {
                $columnDef .= $type;
            } else {
                throw new \RuntimeException("Invalid type for column `$name`.");
            }


            $columnDef .= ($definition['allowNull'] ?? true) ? " NULL" : " NOT NULL";


            if (!empty($definition['primaryKey']))
                $columnDef .= " PRIMARY KEY";


            if (isset($definition['unique']) && $definition['unique'])
                $columnDef .= " UNIQUE";

            if (isset($definition['default']))
                $columnDef .= " DEFAULT " . (is_bool($definition['default']) ? ($definition['default'] ? '1' : '0') : $definition['default']);

            if (!empty($definition['autoIncrement']))
                $columnDef .= " AUTO_INCREMENT";


            $columnDefinitions[] = $columnDef;
        }
        $sql .= implode(",\n", $columnDefinitions) . "\n);";
        return $sql;
    }
}
