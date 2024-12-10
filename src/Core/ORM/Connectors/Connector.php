<?php

namespace Villeon\Core\ORM\Connectors;

use PDO;
use Villeon\Core\ORM\ConnectionWatcher;
use Villeon\Core\ORM\DBVars;

class Connector
{
    use ConnectionWatcher;
    protected function connect(DBVars $config)
    {
        [$username, $password] = [
            $config['username'] ?? null, $config['password'] ?? null,
        ];

        try {
            return $this->createPdoConnection(
                $dsn, $username, $password
            );
        } catch (Exception $e) {
            return $this->tryAgainIfCausedByLostConnection(
                $e, $dsn, $username, $password
            );
        }
    }
    protected function createPdoConnection($dsn, $username, #[\SensitiveParameter] $password, $options): PDO
    {
        return version_compare(phpversion(), '8.4.0', '<')
            ? new PDO($dsn, $username, $password, $options)
            : PDO::connect($dsn, $username, $password, $options);
    }

}