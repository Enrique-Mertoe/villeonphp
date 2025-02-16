<?php

namespace Villeon\Core\ORM\Connectors;

use InvalidArgumentException;
use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;
use Throwable;
use Villeon\Core\ORM\DBVars;
use Villeon\Core\ORM\ORM;

/**
 * Class SQLConnector
 *
 * A database connector that supports multiple database types (SQLite, MySQL, PostgreSQL).
 * It provides methods for executing queries, retrieving single/multiple records,
 * and handling transactions securely.
 */
class SQLConnector extends Connector
{
    public const SQLITE = 0;
    public const MYSQL = 1;
    public const POSTGRES = 2;

    /**
     * SQLConnector constructor.
     *
     * Initializes the database connection based on the specified connector type.
     *
     * @param int $connector One of self::SQLITE, self::MYSQL, self::POSTGRES.
     * @throws InvalidArgumentException If an unsupported connector type is provided.
     */
    public function __construct(int $connector)
    {
        $vars = $this->getDBVars();
        $this->connector = match ($connector) {
            self::SQLITE => new SQLiteConnector,
            self::POSTGRES => new PostGraceConnector($vars),
            self::MYSQL => new MySQLConnector($vars),
            default => throw new InvalidArgumentException("Unsupported connector type: $connector"),
        };
    }

    /**
     * Factory method to create a new SQLConnector instance.
     *
     * @param int $connectorType The database type (default is MySQL).
     * @return SQLConnector The instantiated SQLConnector.
     */
    public static function of(int $connectorType = self::MYSQL): SQLConnector
    {
        return new self($connectorType);
    }

    /**
     * Retrieves database connection credentials from environment variables.
     *
     * @return DBVars The database configuration variables.
     */

    private function getDBVars(): DBVars
    {
        return new DBVars(
            env("DB_SERVER"),
            env("DB_USER"),
            env("DB_PASSWORD"),
            env("DB_NAME"),
        );
    }

    /**
     * Retrieves a single record from the database.
     *
     * @param string $query The SQL query to execute.
     * @param array $data Optional parameters for the query.
     * @return array|bool An associative array containing the record, or false if not found.
     */

    public function getOne(string $query, array $data = []): array|bool
    {
        return $this->execute($query, $data)->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Retrieves multiple records from the database.
     *
     * @param string $query The SQL query to execute.
     * @param array $data Optional parameters for the query.
     * @return array|null An array of associative arrays containing the records, or null if no records found.
     */
    public function getAll(string $query, array $data = []): ?array
    {
        return $this->execute($query, $data)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Executes an SQL query with optional parameters.
     *
     * @param string $query The SQL query to execute.
     * @param array $data Optional parameters for the query.
     * @return false|PDOStatement The executed PDO statement, or false on failure.
     * @throws RuntimeException If a PDOException occurs.
     */
    public function execute(string $query, array $data = []): false|PDOStatement
    {
        try {
            $this->connect();
            $statement = $this->pdo->prepare($query);
            $statement->execute([...$data]);
            return $statement;
        } catch (PDOException $e) {
            throw new RuntimeException($e->getMessage(), 0, $e);
        }
    }

    /**
     * Executes a write (INSERT, UPDATE, DELETE) operation with transaction handling.
     *
     * If no active transaction exists, it starts one. If the operation succeeds, it commits.
     * Otherwise, it rolls back the transaction.
     *
     * @param string $qry The SQL query to execute.
     * @param array $data Optional parameters for the query.
     * @return bool True if the operation succeeds, false otherwise.
     * @throws RuntimeException If an exception occurs during execution.
     */

    public function write(string $qry, array $data = []): bool
    {
        $canTransact = !ORM::inTransaction();
        try {
            if ($canTransact) {
                $this->pdo->beginTransaction();
            }
            $this->execute($qry, $data);
            if ($canTransact) {
                $this->pdo->commit();
            }
            return true;
        } catch (Throwable $e) {
            if ($canTransact && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw new RuntimeException($e->getMessage());
        }
    }
}
