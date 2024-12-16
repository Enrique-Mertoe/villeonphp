<?php

namespace Villeon\Core\ORM\Connectors;

use PDO;
use Villeon\Core\ORM\ConnectionWatcher;
use Villeon\Core\ORM\DBVars;

class SQLConnector extends Connector
{
    public const SQLITE = 0;
    public const MYSQL = 1;
    public const POSTGRES = 2;

    public const FETCH_ONE = 3;
    public const FETCH_ALL = 4;

    public function __construct(int $connector)
    {
        $vars = $this->getDBVars();
        $this->connector = match ($connector) {
            self::SQLITE => new SQLiteConnector,
            self::POSTGRES => new PostGraceConnector($vars),
            self::MYSQL => new MySQLConnector($vars),
            default => throw new \InvalidArgumentException("Unsupported connector type: $connector"),
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

    public function getOne(string $query, array $data = []): array |bool
    {
        return $this->execute($query, $data, self::FETCH_ONE);
    }

    /**
     * @param string $query
     * @param array $data
     * @return array[]|null
     */
    public function getAll(string $query, array $data = []): ?array
    {
        return $this->execute($query, $data, self::FETCH_ALL);
    }

    public function execute(string $query, array $data = [], int $fetchType = null): array|bool
    {
        try {
            $this->connect();
            $statement = $this->pdo->prepare($query);
            $this->pdo->beginTransaction();
            $statement->execute([...$data]);
            $this->pdo->commit();

            return match ($fetchType) {
                self::FETCH_ONE => $statement->fetch(PDO::FETCH_ASSOC),
                self::FETCH_ALL => $statement->fetchAll(PDO::FETCH_ASSOC),
                default => false
            };
        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
