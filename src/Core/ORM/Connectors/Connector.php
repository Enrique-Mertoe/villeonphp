<?php

namespace Villeon\Core\ORM\Connectors;

use Villeon\Core\ORM\ConnectionWatcher;
use Villeon\Core\ORM\DBVars;

abstract class Connector
{
    use ConnectionWatcher;

    protected \PDO $pdo;

    protected MySQLConnector|PostGraceConnector|SQLiteConnector $connector;


    public function connect(): static
    {
        [$username, $password] = $this->connector->getCredentials();

        try {
            $this->pdo = $this->createPdoConnection(
                $this->connector->getDsn(), $username, $password, []
            );
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
        return $this;
    }

    protected function createPdoConnection($dsn, $username, #[\SensitiveParameter] $password, $options): \PDO
    {
        return version_compare(phpversion(), '8.4.0', '<')
            ? new \PDO($dsn, $username, $password, $options)
            : \PDO::connect($dsn, $username, $password, $options);
    }
}
