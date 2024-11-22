<?php

namespace Villeon\DB;

class DBOptions
{
    public string $HOST;
    public string $USER;
    public string $PASSWORD;
    public string $NAME;

    /**
     * @param string $host
     * @param string $user
     * @param string $password
     * @param string $name
     */
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
