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

    /**
     * @var PDO The active PDO database connection.
     */
    protected PDO $pdo;

    /**
     * Retrieves the current PDO connection instance.
     *
     * @return PDO The active PDO connection.
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * @var  MySQLConnector|PostGraceConnector|SQLiteConnector $connector Holds the active database connector instance.
     */
    protected MySQLConnector|PostGraceConnector|SQLiteConnector $connector;


    /**
     * Establishes a database connection.
     *
     * If the ORM is running in a live environment, it retrieves the existing PDO instance.
     * Otherwise, it initializes a new PDO connection.
     *
     * @return static The current instance with an active PDO connection.
     */
    public function connect(): static
    {
        if (ORM::isLive()) {
            $this->pdo = ORM::getPDO();
        } else {
            $this->pdo = $this->initPDO();
        }
        return $this;
    }

    /**
     * Initializes a new PDO connection.
     *
     * Retrieves database credentials from the connector and establishes a new connection
     * with strict error handling and secure configurations.
     *
     * @return PDO The newly created PDO instance.
     * @throws RuntimeException If the database connection fails.
     */

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
