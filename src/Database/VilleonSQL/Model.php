<?php

namespace Villeon\Database\VilleonSQL;

use PDO;
use Villeon\Database\VilleonSQL\Connection\Connect;
use Villeon\Database\VilleonSQL\DataTypes\DataTypes;

class Model
{
    private PDO $pdo;
    private string $table;

    private array $attributes;

    /**
     * @var Model[]
     */
    private static array $model_definitions = [];

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function init_model(): void
    {
        $this->attributes["__table_name__"] = $this->table;
        $qry = QueryBuilder::fromAttributes($this->attributes);
        $this->pdo->exec($qry);

    }

    private function setTable($table): Model
    {
        $this->table = $table;
        return $this;
    }

    private function setAttributes(array $attr): void
    {
        $attr = array_merge([
            "id" => [
                "type" => DataTypes::INTEGER(),
                "autoIncrement" => true,
                "primaryKey" => true,
                "allowNull" => false
            ],
        ], $attr);
        $this->attributes = $attr;
    }

    public function findAll(array $options)
    {
        $sql = new QueryBuilder($this->table);
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @param array<string,mixed> $data
     * @return $this
     */
    public function create(array $data): static
    {
        try {
            $sql = (new QueryBuilder($this->table))->insert($data);
            $this->pdo->prepare($sql['sql'])->execute($sql['values']);
        } catch (\Exception $e) {

        }
        return $this;
    }

    // Save or Update record
    public function save($data)
    {
        if (isset($data['id'])) {
            // Update existing record
            $fields = '';
            foreach ($data as $key => $value) {
                if ($key != 'id') {
                    $fields .= "$key = :$key, ";
                }
            }
            $fields = rtrim($fields, ', ');
            $stmt = $this->pdo->prepare("UPDATE {$this->table} SET $fields WHERE id = :id");
            $stmt->execute($data);
        } else {
            // Insert new record
            $columns = implode(", ", array_keys($data));
            $placeholders = ":" . implode(", :", array_keys($data));
            $stmt = $this->pdo->prepare("INSERT INTO {$this->table} ($columns) VALUES ($placeholders)");
            $stmt->execute($data);
        }
    }

    // Delete record
    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public static function define(string $name, array $attributes = []): Model
    {
        $model = new Model((new Connect(VilleonSQL::$options))->getConnection());
        $model->setTable($name)->setAttributes($attributes);
        self::$model_definitions[] = $model;
        return $model;
    }

    public static function getAll(): array
    {
        try {
            return ModelFactory::getInstance()->getAll();
        } catch (\Exception) {
            return [];
        }
    }

    public static function removeModel($name): bool
    {
        try {
            return ModelFactory::getInstance()->removeModel($name);
        } catch (\Exception) {
            return false;
        }
    }

    public static function infoSchema($name): array
    {
        try {
            return ModelFactory::getInstance()->infoSchema($name);
        } catch (\Exception) {
            return [];
        }
    }

}
