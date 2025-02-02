<?php

namespace Villeon\DB;

use RuntimeException;
use Villeon\Core\ORM\Connectors\ConnectionFactory;
use Villeon\Core\ORM\Connectors\SQLConnector;
use Villeon\Core\ORM\FieldSchema;
use Villeon\Core\ORM\Models\QRYBuilder;
use Villeon\DB\VilleonSQL\DataTypes\AbstractDataType;
use Villeon\Http\Request;
use Villeon\Manager\Manager;

class ModelHandler
{
    private string $name;
    private ?string $alias;
    /**
     * @var ColumnField[]
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
     * @param ColumnField[] $attributes
     * @return ModelHandler
     */
    public static function define($name, ?string $alias, ?array $attributes): ModelHandler
    {
        return new static($name, $alias, $attributes);
    }

    public function create(): string|bool
    {
        $mode = Request::args("mode") ?? "new";
        if ($mode !== "edit" && Manager::modelExists($this->name)) {
            return "Model " . ucfirst($this->name) . " already exits";
        }

        [$suc, $data] = Manager::createModel($this->name, $this->alias, $this->attributes, $mode === "edit");
        try {
            if (($suc === true) && file_exists($data)) {
                require_once $data;
                $namespace = "App\\Models\\" . ucfirst($this->name);
                if (class_exists($namespace)) {
                    return $this->addonDB($namespace, $mode);
                }
            }

            return "Something went wrong!";
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }

    public function addonDB($class, $mode): string
    {
        $schema = new FieldSchema();
        $schema->table(strtolower($this->name) . "s");
        (new $class())->schema($schema);
//        $sql = QRYBuilder::from($schema, $mode === "edit", $existing);
        $sql = QRYBuilder::from($schema);
        try {
            SQLConnector::of()->execute($sql);
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
        return "Model " . ucfirst($this->name) . " is created in Models directory.";
    }

    private static function getExistingColumns(string $tableName): array
    {
        $result = SQLConnector::of()->getAll("SHOW COLUMNS FROM `$tableName`");
        print_r($result);
        $columns = [];
        foreach ($result as $row) {
            $columns[$row['Field']] = strtoupper($row['Type']);
        }

        return $columns;
    }


    public function getAttributes(array $attributes): string
    {

        $tableName = $this->alias ?: $this->name ?? throw new RuntimeException('Table name not defined.');
        if (!str_ends_with($tableName, 's')) {
            $tableName .= 's';
        }

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


            $columnDef .= ($definition['nullable'] === "true") ? " NULL" : " NOT NULL";


            if (!empty($definition['primary']) && $definition['primary'] !== "false")
                $columnDef .= " PRIMARY KEY";


            if (isset($definition['unique']) && $definition['unique'] === "true")
                $columnDef .= " UNIQUE";

            if (isset($definition['default']))
                $columnDef .= " DEFAULT " . (is_bool($definition['default']) ? ($definition['default'] ? '1' : '0') : $definition['default']);

            if (isset($definition['auto']) && $definition['auto'] === "true")
                $columnDef .= " AUTO_INCREMENT";


            $columnDefinitions[] = $columnDef;
        }
        $sql .= implode(",\n", $columnDefinitions) . "\n);";
        return $sql;
    }
}
