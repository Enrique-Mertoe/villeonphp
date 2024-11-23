<?php

namespace Villeon\Database\VilleonSQL\Connection;

use PDOException;
use PDO;

class Connect
{
    private PDO $pdo;

    /**
     * @param DBOptions $options
     */
    public function __construct(DBOptions $options)
    {
        try {
            $dsn = "mysql:host=$options->HOST;dbname=$options->NAME";
            $this->pdo = new PDO($dsn, $options->USER, $options->PASSWORD);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw $e;
        }
    }

    /**
     * @return PDO
     */
    public function getConnection(): PDO
    {
        return $this->pdo;
    }
}
