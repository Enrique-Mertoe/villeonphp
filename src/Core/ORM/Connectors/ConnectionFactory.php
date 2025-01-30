<?php

namespace Villeon\Core\ORM\Connectors;

class ConnectionFactory
{
    public const  SQLITE = 0;
    public const  MYSQL = 1;
    public const  POSTGRES = 2;

    public function __construct(int $connector)
    {

    }

    public static function of(int $connector): static
    {
        return new static($connector);
    }

}
