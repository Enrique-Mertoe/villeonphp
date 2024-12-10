<?php

namespace Villeon\Core\ORM\Connectors;

class ConnectionFactory
{
    public const int SQLITE = 0;
    public const int MYSQL = 1;
    public const int POSTGRES = 2;

    public function __construct(int $connector)
    {

    }

    public static function of(int $connector): static
    {
        return new static($connector);
    }

}