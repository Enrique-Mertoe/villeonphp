<?php

namespace Villeon\Core\ORM;

use Exception;
use PDO;
use PDOStatement;
use RuntimeException;
use Villeon\Core\ORM\Connectors\SQLConnector;

class ORM
{
    private static ?PDO $pdo = null;

    // Get the singleton PDO instance
    public static function getPDO(): PDO
    {
        if (!self::$pdo) {
            try {
                self::$pdo = SQLConnector::of()->initPDO();
            } catch (Exception $e) {
                throw new RuntimeException("Database connection failed: " . $e->getMessage());
            }
        }
        return self::$pdo;
    }

    public static function isLive(): bool
    {
        return self::$pdo !== null;
    }

    public static function beginTransaction(): void
    {
        self::getPDO()->beginTransaction();
    }

    public static function commit(): void
    {
        self::getPDO()->commit();
    }

    public static function rollback(): void
    {
        self::getPDO()->rollBack();
    }

    public static function query(string $sql, array $params = []): false|PDOStatement
    {
        try {
            $stmt = self::getPDO()->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (\PDOException $e) {
            if (self::getPDO()->inTransaction()) {
                self::rollback();
            }
            throw $e;
        }
    }
}
