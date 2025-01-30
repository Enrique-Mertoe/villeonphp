<?php

namespace Villeon\Core\ORM\Models;

use Villeon\Core\ORM\Connectors\SQLConnector;
use Villeon\Core\ORM\OrderMode;

/**
 *
 */
class ModelFactory
{
    public const ORDER_ASC = 'ASC';
    public const ORDER_DESC = 'DESC';
    private $model;
    private SQLConnector $connector;
    private QRYBuilder $query;
    private string $table;

    public function __construct($model)
    {
        $this->model = $model;
        $this->connector = SQLConnector::of(SQLConnector::MYSQL);
        $this->table = $this->getTableName();
        $this->query = new QRYBuilder($this->table);
    }

    public function create(array $data): object
    {
        [$sql, $vals] = $this->query->insert($data);
        try {
            $this->connector->write($sql, $vals);
        } finally {

            return $this->fillValues($data, new $this->model);
        }
    }

    public function all(): array
    {
        [$sql, $data] = $this->query->sel("*")->get();
        $result = $this->connector->connect()->getAll($sql, $data);
        $out = [];
        foreach ($result as $item) {
            $instance = new $this->model;
            $this->fillValues($item, $instance);
            $out[] = $instance;
        }
        return $out;
    }

    public function first(): ?object
    {
        [$sql, $data] = $this->query->sel("*")->limit(1)->get();
        try {
            $result = $this->connector->connect()->getOne($sql, $data);
        } catch (\Exception $e) {
            $result = null;
            log_error($e);
        }
        if ($result) {
            return $this->fillValues($result, new $this->model);
        }
        return null;
    }

    public function find($key): ?object
    {
        $this->filter("id", "=", $key);
        return $this->first();
    }

    public function filter($col, $operator = null, $value = null): self
    {
        $this->query->condition("AND", $col, $value, $operator, func_num_args() === 2);
        return $this;
    }

    public function and($col, $operator = null, $value = null): self
    {
        $this->query->condition("AND", $col, $value, $operator, func_num_args() === 2);
        return $this;
    }

    public function or($col, $operator = null, $value = null): self
    {
        $this->query->condition("OR", $col, $value, $operator, func_num_args() === 2);
        return $this;
    }

    public function orderBy(array|string $columns, OrderMode $direction = OrderMode::ASC): self
    {
        if (is_string($columns)) {
            $columns = [$columns => $direction];
        }
        $this->query->orderBy($columns);
        return $this;
    }

    public function limit(...$limits): static
    {
        $this->query->limit(...$limits);
        return $this;
    }

    private function fillValues(array $data, object $model): object
    {
        foreach ($data as $key => $value) {
//            if (property_exists($model, $key))
            $model->$key = $value;
        }
        return $model;
    }

    private function getTableName(): string
    {
        $instance = new $this->model;
        $className = class_basename($this->model);
        if (property_exists($instance, "tableName")) {
            return $instance->tableName;
        }
        return strtolower($className) . 's';
    }

}
