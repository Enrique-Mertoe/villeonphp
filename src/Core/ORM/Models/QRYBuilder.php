<?php

namespace Villeon\Core\ORM\Models;

use InvalidArgumentException;
use Villeon\Core\ORM\ColField;
use Villeon\Core\ORM\DataType\DataType;
use Villeon\Core\ORM\FieldSchema;
use Villeon\Core\ORM\OrderMode;
use Villeon\Library\Collection\Collection;
use Villeon\Library\Collection\Dict;

/**
 *
 */
class QRYBuilder
{
    /**
     * @var string
     */
    private string $ref;

    private array $queries;

    public Collection $values;

    /**
     * @param string $ref
     */
    public function __construct(string $ref)
    {
        $this->ref = $ref;
        $this->queries = [];
        $this->values = mutableListOf();
    }

    /**
     * @param string|array|null $selectors
     * @return $this
     */
    public function sel(string|array|null $selectors = null): static
    {
        $this->queries["selector"] = $selectors;
        return $this;
    }

    /**
     * @param string|array $columns
     * @return $this
     */
    public function orderBy(string|array $columns): self
    {
        $this->queries["order"] = $columns;
        return $this;
    }

    public function prepareValueAndOperator($value, $operator, $useDefault = false): array
    {
        if ($useDefault) {
            return [$operator, '='];
        }
        return [$value, $operator];
    }

    public function condition(string $type, $col, $value, $operator, bool $default = false): self
    {
        if (!in_array($type, ["AND", "OR"])) {
            throw new InvalidArgumentException("Invalid condition type: $type");
        }
        [$value, $operator] = $this->prepareValueAndOperator($value, $operator, $default);

        if (!in_array($operator, ['=', '!=', '>', '<', '>=', '<=', 'LIKE'])) {
            throw new InvalidArgumentException("Invalid operator: $operator");
        }


        $this->queries["conditions"][] = [
            'type' => $type,
            'col' => $col,
            'value' => $value,
            'operator' => $operator,
        ];

        return $this;
    }

    private function getFilter(): string
    {
        if (empty($this->queries["conditions"])) {
            return "";
        }

        $filterStr = " WHERE ";
        $lastType = "";

        foreach ($this->queries["conditions"] as $condition) {
            $type = $condition['type'];
            $lastType = $type;
            $col = $condition['col'];
            $operator = $condition['operator'];
            $value = $condition['value'];

            $filterStr .= "$col $operator ? $type ";
            $this->values->add($value);
        }
        return rtrim($filterStr, " $lastType ");
    }

    /**
     * @param mixed ...$limit
     * @return $this
     */
    public function limit(...$limit): static
    {
        $this->queries["limit"] = $limit;
        return $this;
    }

    /**
     * @return string
     */
    private function join(): string
    {
        $sql = $this->getAction();
        $sql .= $this->getFilter();
        $sql .= $this->getOrder();
        $sql .= $this->getLimits();
        return preg_replace('/\s+/', " ", $sql);
    }

    /**
     * @return string
     */
    private function getAction(): string
    {
        $sql = "";
        if ($select = $this->queries["selector"]) {
            $sql .= "SELECT";
            if (is_string($select)) {
                $sql .= " " . trim($select) . " ";
            } elseif (is_array($select)) {
                $sql .= " " . implode(",", $select) . " ";
            } else {
                $sql .= " * ";
            }
            $sql .= " FROM $this->ref";
        }

        return $sql;

    }

    /**
     * @param QRYBuilder $builder
     * @return string
     */
    public static function buildQuery(QRYBuilder $builder): string
    {
        return $builder->join();
    }

    /**
     * @return array
     */
    public function get(): array
    {
        return [$this->join(), $this->values->toArray()];

    }

    private function getLimits(): string
    {

        if (!$limits = ($this->queries["limit"] ?? []))
            return "";
        $limits = array_slice($limits, 0, 2);
        return " LIMIT " . implode(",", $limits);
    }

    private function getOrder(): string
    {
        if (!$order = ($this->queries["order"] ?? []))
            return "";
        $orderBy = [];
        foreach ($order as $column => $direction) {
            if (!in_array($direction, [OrderMode::ASC, OrderMode::DESC], true)) {
                throw new \InvalidArgumentException("Invalid order direction: $direction");
            }
            $direction = $direction->name;
            $orderBy[] = "`$column` $direction";
        }
        return " ORDER BY " . implode(", ", $orderBy);
    }

    public function insert(array $data): array
    {
        if (empty($data)) {
            throw new InvalidArgumentException("Insert data cannot be empty.");
        }
        $vals = implode(",", array_fill(0, count($data), "?"));
        $cols = implode(",", array_map(fn($col) => "`$col`", array_keys($data)));
        $sql = "INSERT INTO $this->ref ($cols) VALUES($vals)";
        return [str($sql), array_values($data)];
    }

    public static function from(FieldSchema $schema, bool $alter = false, $existing = null): string
    {
        $tableName = $schema->table;
        $columns = [];

        foreach ($schema->fields as $name => $field) {
            $column = "`$name` " . self::getSQLType($field);

            if ($field->isPrimary) {
                $column .= " PRIMARY KEY";
            }
            if ($field->autoValue) {
                $column .= " AUTO_INCREMENT";
            }
            if ($field->isUnique) {
                $column .= " UNIQUE";
            }
            if (!$field->allowNull) {
                $column .= " NOT NULL";
            }

            $columns[$name] = $column;
        }

        if (!$alter) {
            // Create Table
            return "CREATE TABLE IF NOT EXISTS `$tableName` (" . implode(", ", $columns) . ");";
        } else {
            $existingColumns = $existing;
            $alterStatements = [];
            foreach ($existingColumns as $existingName => $existingType) {
                if (!isset($columns[$existingName])) {
                    $alterStatements[] = "DROP COLUMN `$existingName`";
                }
            }
            foreach ($columns as $name => $columnDefinition) {
                if (!isset($existingColumns[$name])) {
                    // New field → Add column
                    $alterStatements[] = "ADD COLUMN $columnDefinition";
                } elseif ($existingColumns[$name] !== $columnDefinition) {
                    // Modified field → Change column
                    $alterStatements[] = "MODIFY COLUMN $columnDefinition";
                }
            }
            if (empty($alterStatements)) {
                return "";
            }
            return "ALTER TABLE `$tableName` " . implode(", ", $alterStatements) . ";";
        }
    }

    private static function getSQLType(ColField $field): string
    {
        if ($field->type instanceof DataType) {
            return $field->type->toSql($field->default);
        }
        $type = (match ($field->type) {
            DataType::STRING => "VARCHAR(" . ($field->length ?? 255) . ")",
            DataType::INT => "INT",
            DataType::BOOL => "TINYINT(1)",
            DataType::DATE => "DATE",
            default => "TEXT"
        });
        $type .= ($field->default !== null) ? (" DEFAULT " . (match ($field->type) {
                DataType::STRING => "'$field->default'",
                default => $field->default,
            })) : "";
        return $type;
    }

    public function update(object|array $info, $cols): array
    {
        // Convert object to array if needed
        $data = is_object($info) ? get_object_vars($info) : $info;

        if (!isset($data['id'])) {
            throw new InvalidArgumentException("Update operation requires an 'id' field.");
        }

        // Extract the ID and remove it from the update data
        $id = $data['id'];
        unset($data['id']);

        if (empty($data)) {
            throw new InvalidArgumentException("No valid fields to update.");
        }

        // Get valid database columns for this model
        $validColumns = $cols;

        // Filter only valid columns
        $filteredData = array_intersect_key($data, array_flip($validColumns));

        if (empty($filteredData)) {
            throw new InvalidArgumentException("No valid database columns found for update.");
        }

        // Generate the SET clause dynamically
        $setClause = implode(", ", array_map(static fn($col) => "`$col` = ?", array_keys($filteredData)));
        // SQL statement
        $sql = "UPDATE `$this->ref` SET $setClause WHERE `id` = ?";

        // Prepare values for binding
        $values = array_values($filteredData);
        $values[] = $id; // Append ID for the WHERE clause
        return [$sql, $values, $id];
    }

    public function delete(object|array $filters, array $validColumns): array
    {
        // Convert object to array if needed
        $data = is_object($filters) ? get_object_vars($filters) : $filters;

        // Get valid database columns to filter out unwanted fields
        $filteredData = array_intersect_key($data, array_flip($validColumns));

        if (empty($filteredData)) {
            throw new InvalidArgumentException("No valid filters provided for deletion.");
        }
        if (isset($filteredData["id"])) {
            $filteredData = ["id" => $filteredData["id"]];
        }

        // Build the WHERE clause dynamically
        $conditions = implode(" AND ", array_map(static fn($col) => "`$col` = ?", array_keys($filteredData)));

        // SQL delete query
        $sql = "DELETE FROM `$this->ref` WHERE $conditions";
        return [$sql, array_values($filteredData)];
    }

    public function count(): static
    {
        $this->queries["selector"] = "COUNT(*)";
        return $this;
    }

}
