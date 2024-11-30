<?php

namespace Villeon\Database\VilleonSQL;

use PDO;
use Villeon\Database\VilleonSQL\Connection\Connect;
use function Symfony\Component\String\s;

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

    public function removeModel($name): bool
    {
        try {
            $this->pdo->exec("DROP TABLE IF EXISTS $name;");
            return true;
        } catch (\Exception) {
            return false;
        }
    }

    public function infoSchema($name): array
    {
        try {
            $db_name = env("DB_NAME");
            $stmt = $this->pdo->prepare("
                SELECT 
                    c.COLUMN_NAME,
                    c.DATA_TYPE,
                    c.IS_NULLABLE,
                    c.COLUMN_DEFAULT,
                    c.EXTRA,
                    kcu.CONSTRAINT_NAME,
                    kcu.COLUMN_NAME AS PRIMARY_KEY
                FROM INFORMATION_SCHEMA.COLUMNS c
                LEFT JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE kcu
                    ON c.TABLE_SCHEMA = kcu.TABLE_SCHEMA
                    AND c.TABLE_NAME = kcu.TABLE_NAME
                    AND c.COLUMN_NAME = kcu.COLUMN_NAME
                WHERE c.TABLE_SCHEMA = :dbName
                  AND c.TABLE_NAME = :tableName
            ");
            $stmt->bindParam(':dbName', $db_name, PDO::PARAM_STR);
            $stmt->bindParam(':tableName', $name, PDO::PARAM_STR);
            $stmt->execute();
            $res = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
                $res[] = ColumnInfo::instance($col);
            }
            return $res;
        } catch (\Exception) {
            return [];
        }
    }

}