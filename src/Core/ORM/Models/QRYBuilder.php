<?php

namespace Villeon\Core\ORM\Models;

use InvalidArgumentException;
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
            print_r(9090);
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
            if (is_array($select))
                $sql .= " " . implode(",", $select) . " ";
            else
                $sql .= " * ";
            $sql .= " FROM $this->ref";
        }

        return $sql;

    }

    /**
     * @return string
     */
//    private function getFilter(): string
//    {
//        if (!$filters = $this->queries["filter"])
//            return "";
//        $filter_str = str(" WHERE ");
//        foreach ($filters as $key => $value) {
//            $filter_str->append("$key = ? AND ");
//            $this->values->add($value);
//        }
//        $filter_str->trimEnd(" AND ");
//        return $filter_str;
//    }

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
}
