<?php

namespace Villeon\Core\ORM;

class DBVars
{
    public string $HOST;
    public string $USER;
    public string $PASSWORD;
    public string $NAME;


    public function __construct(
        string $host,
        string $user,
        string $password,
        string $name,
    )
    {
        $this->HOST = $host;
        $this->USER = $user;
        $this->PASSWORD = $password;
        $this->NAME = $name;
    }
}