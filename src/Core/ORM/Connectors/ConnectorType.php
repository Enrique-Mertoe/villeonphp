<?php

namespace Villeon\Core\ORM\Connectors;

interface ConnectorType
{
    public function getDsn(): string;

    public function getCredentials(): array;
}
