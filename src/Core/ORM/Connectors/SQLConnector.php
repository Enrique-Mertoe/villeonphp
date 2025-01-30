<?php

namespace Villeon\Core\ORM\Connectors;

use InvalidArgumentException;
use PDO;
use PDOException;
use Villeon\Core\ORM\ConnectionWatcher;
use Villeon\Core\ORM\DBVars;

class SQLConnector extends Connector
{
    public const SQLITE = 0;
    public const MYSQL = 1;
    public const POSTGRES = 2;

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

    public static function of(int $connectorType = self::MYSQL): SQLConnector
    {
        return new SQLConnector($connectorType);
    }

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
     * @param string $query
     * @param array $data
     * @return array|null
     */

    public function getOne(string $query, array $data = []): array|bool
    {
        return $this->execute($query, $data)->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @param string $query
     * @param array $data
     * @return array[]|null
     */
    public function getAll(string $query, array $data = []): ?array
    {
        return $this->execute($query, $data)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function execute(string $query, array $data = []): false|\PDOStatement
    {
        try {
            $this->connect();
            $this->pdo->beginTransaction();
            $statement = $this->pdo->prepare($query);
            $statement->execute([...$data]);
            return $statement;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function write($qry, $data = []): bool
    {
        $this->execute($qry, $data);
        $this->pdo->commit();
        return true;
    }
}
