<?php

namespace Villeon\Core\ORM\Models;

use Villeon\Core\ORM\Connectors\SQLConnector;

/**
 * @template T
 */
class ModelBuilder
{
    /**
     * @var SQLConnector
     */
    public SQLConnector $connector;
    /**
     * @var T
     */
    private $model;
    /**
     * @var string
     */
    private string $table;
    /**
     * @var QRYBuilder
     */
    private QRYBuilder $query;

    /**
     * @param T $model
     */
    public function __construct($model)
    {
        $this->connector = SQLConnector::of(SQLConnector::MYSQL);
        $this->model = $model;
        $this->table = $this->getTableName();
        $this->query = new QRYBuilder($this->table);
    }

    /**
     * @return string
     */
    private function getTableName(): string
    {
        $instance = new  $this->model;
        if (property_exists($instance, "tableName")) {
            return $instance->tableName;
        }
        return strtolower($this->model) . 's';
    }


    /**
     * @return T[]
     */
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

    /**
     * @return T
     */
    public function first(): object
    {
        [$sql, $data] = $this->query->sel("*")->limit(1)->get();
        try {
            $result = $this->connector->connect()->getOne($sql, $data);
        } catch (\Exception $e) {
            $result = null;
            log_error($e);
        }
        $instance = new $this->model;
        if ($result) {
            $this->fillValues($result, $instance);
        }
        return $instance;
    }

    /**
     * @param array $filters
     * @return $this
     */
    public function filterBy(array $filters): self
    {
        $this->query->filterBy($filters);
        return $this;
    }

    /**
     * @param array|string $columns
     * @param string $direction
     * @return $this
     */
    public function orderBy(array|string $columns, string $direction = 'ASC'): self
    {
        $this->query->orderBy($columns, $direction);
        return $this;
    }

    public function limit(...$limits): static
    {
        $this->query->limit(...$limits);
        return $this;
    }

    /**
     * @param array $data
     * @param object $model
     * @return void
     */
    private function fillValues(array $data, object $model): void
    {
        foreach ($data as $key => $value) {
            if (property_exists($model, $key))
                $model->$key = $value;
        }
    }
}
