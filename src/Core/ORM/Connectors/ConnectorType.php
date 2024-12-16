<?php

namespace Villeon\Core\ORM\Connectors;

interface ConnectorType
{
    function getDsn():string;
    function getCredentials():array;
}
