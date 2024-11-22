<?php

namespace Villeon\Database\VilleonSQL;

use Exception;
use PDO;
use Villeon\Database\VilleonSQL\DataTypes\AbstractDataType;

class QueryBuilder
{
    private PDO $pdo;

    /**
     * Table name for the given query
     * @var string
     */
    private string $table;

    /**
     * @var array
     */
    private array $where = [];

    /**
     * @param $table
     */
    public function __construct($table)
    {
        $this->table = $table;
    }

    /**
     * @param array $data
     * @return array<string,mixed>
     * @throws Exception
     */
    public function insert(array $data): array
    {
        $tableName = $this->table;
        if (empty($data)) {
            throw new Exception('No data provided for insertion.');
        }

        $columns = implode("`, `", array_keys($data));

        $placeholders = implode(", ", array_fill(0, count($data), "?"));
        $values = array_map(function ($value) {
            return $value;
        }, array_values($data));

        $sql = "INSERT " . "INTO `$tableName` (`$columns`) VALUES ($placeholders);";

        return [
            'sql' => $sql,
            'values' => $values
        ];
    }

    public function where($column, $operator, $value): static
    {
        $this->where[] = "$column $operator :$column";
        return $this;
    }

    public function get(): false|array
    {
        $sql = "SELECT " . "* FROM {$this->table}";
        if ($this->where) {
            $sql .= " WHERE " . implode(' AND ', $this->where);
        }

        $stmt = $this->pdo->prepare($sql);
        foreach ($this->where as $condition) {
            $column = explode(' ', $condition)[0];
            $stmt->bindValue(":$column", $value);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @throws Exception
     */
    public static function fromAttributes(array $attributes): string
    {

        $tableName = $attributes['__table_name__'] ?? throw new Exception('Table name not defined.');

        $columns = $attributes;
        unset($columns['__table_name__']);

        $sql = "CREATE " . "TABLE IF NOT EXISTS `$tableName` (\n";

        $columnDefinitions = [];

        foreach ($columns as $name => $definition) {
            $columnDef = "`$name` ";

            $type = $definition['type'] ?? throw new Exception("Type not defined for column `$name`.");
            if ($type instanceof AbstractDataType) {
                $columnDef .= $type->toSql();
            } elseif (is_string($type)) {
                $columnDef .= $type;
            } else {
                throw new Exception("Invalid type for column `$name`.");
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
