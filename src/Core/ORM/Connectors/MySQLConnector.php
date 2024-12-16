<?php

namespace Villeon\Core\ORM\Connectors;

use Villeon\Core\ORM\DBVars;

class MySQLConnector implements ConnectorType
{
    private DBVars $vars;

    public function __construct(DBVars $vars)
    {
        $this->vars = $vars;
    }

    function getDsn(): string
    {
        return "mysql:host={$this->vars->HOST};dbname={$this->vars->NAME};charset=utf8mb4";
    }

    function getCredentials(): array
    {
        return [$this->vars->USER,$this->vars->PASSWORD,
        ];
    }
}
