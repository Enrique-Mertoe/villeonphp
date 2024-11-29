<?php

namespace Villeon\Database\VilleonSQL;

use PDO;
use Villeon\Database\VilleonSQL\Connection\Connect;

class ModelFactory
{
    private PDO $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public static function getInstance(): static
    {
        return new static((new Connect(VilleonSQL::$options))->getConnection());
    }

    public function getAll(): array
    {
        try {
            $stmt = $this->pdo->query("SHOW TABLES;");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (\Exception) {
            return [];
        }
    }
}