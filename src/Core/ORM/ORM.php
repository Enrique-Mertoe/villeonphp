<?php

namespace Villeon\Core\ORM;

use Exception;
use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;
use Villeon\Core\ORM\Connectors\SQLConnector;

class ORM
{
    private static ?PDO $pdo = null;

    /**
     * Retrieves the singleton PDO instance, initializing it if necessary.
     *
     * @return PDO The active PDO connection.
     * @throws RuntimeException If the database connection fails.
     */
    public static function getPDO(): PDO
    {
        if (!isset(self::$pdo)) {
            self::initializePDO();
        }
        return self::$pdo;
    }

    /**
     * Initializes the PDO connection using the SQLConnector.
     *
     * @throws RuntimeException If the database connection fails.
     */
    private static function initializePDO(): void
    {
        try {
            self::$pdo = SQLConnector::of()->initPDO();
        } catch (Exception $e) {
            throw new RuntimeException("Database connection failed: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Checks if a database connection is active.
     *
     * @return bool True if connected, false otherwise.
     */
    public static function isLive(): bool
    {
        return isset(self::$pdo);
    }

    /**
     * Checks if a transaction is currently active.
     *
     * @return bool True if a transaction is ongoing, false otherwise.
     */
    public static function inTransaction(): bool
    {
        return self::isLive() && self::$pdo->inTransaction();
    }

    /**
     * Begins a database transaction.
     *
     * @throws RuntimeException If starting the transaction fails.
     */
    public static function beginTransaction(): void
    {
        self::getPDO()->beginTransaction();
    }

    /**
     * Commits the current transaction.
     *
     * @throws RuntimeException If committing fails.
     */
    public static function commit(): void
    {
        self::getPDO()->commit();
    }

    /**
     * Rolls back the current transaction.
     *
     * @throws RuntimeException If rollback fails.
     */
    public static function rollback(): void
    {
        self::getPDO()->rollBack();
    }

    /**
     * Executes a SQL query with prepared statements.
     *
     * @param string $sql The SQL query string.
     * @param array $params Parameters to bind to the query.
     * @return false|PDOStatement The prepared statement result.
     * @throws RuntimeException If execution fails.
     */
    public static function query(string $sql, array $params = []): false|PDOStatement
    {
        try {
            $stmt = self::getPDO()->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            if (self::inTransaction()) {
                self::rollback();
            }
            throw new RuntimeException("Query execution failed: " . $e->getMessage(), 0, $e);
        }
    }
}
