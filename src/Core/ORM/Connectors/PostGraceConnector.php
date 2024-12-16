<?php

namespace Villeon\Core\ORM\Connectors;

use Villeon\Core\ORM\DBVars;

class PostGraceConnector implements ConnectorType
{
    private DBVars $vars;

    public function __construct(DBVars $vars)
    {
        $this->vars = $vars;
    }

    function getDsn(): string
    {
        return "pgsql:host={$this->vars->HOST}};dbname={$this->vars->NAME}";
    }

    function getCredentials(): array
    {
        return [$this->vars->USER, $this->vars->PASSWORD,
        ];
    }
}
