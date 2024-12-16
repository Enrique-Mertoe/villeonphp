<?php

namespace Villeon\Core\ORM\Models;

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
    /**
     * @var Dict
     */
    private Dict $queries;

    public Collection $values;

    /**
     * @param string $ref
     */
    public function __construct(string $ref)
    {
        $this->ref = $ref;
        $this->queries = \dict();
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
     * @param string $direction
     * @return $this
     */
    public function orderBy(string|array $columns, string $direction = 'ASC'): self
    {
        $this->queries["order"] = $columns;
        return $this;
    }

    /**
     * @param array $filters
     * @return $this
     */
    public function filterBy(array $filters): self
    {
        $this->queries["filter"] = $filters;
        return $this;
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
    private function getFilter(): string
    {
        if (!$filters = $this->queries["filter"])
            return "";
        $filter_str = str(" WHERE ");
        foreach ($filters as $key => $value) {
            $filter_str->append("$key = ? AND ");
            $this->values->add($value);
        }
        $filter_str->trimEnd(" AND ");
        return $filter_str;
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

        if (!$limits = $this->queries["limit"])
            return "";
        $limits = array_slice($limits, 0, 2);
        return " LIMIT " . implode(",", $limits);
    }

    private function getOrder(): string
    {
        if (!$order = $this->queries["order"])
            return "";
        $orderBy = [];
        foreach ($order as $column => $direction) {
            $direction = strtoupper($direction);
            if (!in_array($direction, ['ASC', 'DESC'])) {
                $direction = 'ASC';
            }
            $orderBy[] = "`$column` ?";
            $this->values->add($direction);
        }
        return " ORDER BY " . implode(", ", $orderBy);
    }
}
