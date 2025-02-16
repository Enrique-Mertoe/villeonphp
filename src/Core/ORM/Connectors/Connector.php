<?php

namespace Villeon\Core\ORM\Connectors;

use Exception;
use PDO;
use RuntimeException;
use SensitiveParameter;
use Villeon\Core\ORM\ConnectionWatcher;
use Villeon\Core\ORM\DBVars;
use Villeon\Core\ORM\ORM;

abstract class Connector
{
    use ConnectionWatcher;

    protected PDO $pdo;

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    protected MySQLConnector|PostGraceConnector|SQLiteConnector $connector;


    public function connect(): static
    {
        if ($pdo = ORM::getPDO()) {
            $this->pdo = $pdo;
        } else {
            $this->pdo = $this->initPDO();
        }
        return $this;
    }

    public function initPDO(): PDO
    {
        try {
            [$username, $password] = $this->connector->getCredentials();
            return new PDO($this->connector->getDsn(), $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (Exception $e) {
            throw new RuntimeException("Database connection failed: " . $e->getMessage());
        }
    }
}
