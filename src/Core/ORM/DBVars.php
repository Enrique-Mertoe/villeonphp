<?php

namespace Villeon\Core\ORM;

/**
 *
 */
class DBVars
{
    /**
     * @var string
     */
    public string $HOST;
    /**
     * @var string
     */
    public string $USER;
    /**
     * @var string
     */
    public string $PASSWORD;
    /**
     * @var string
     */
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
