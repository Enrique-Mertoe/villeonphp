<?php

namespace Villeon\Core\Exceptions;

use http\Exception\RuntimeException;

class ExceptionManager
{
    public function __construct()
    {
    }

    public static function create($message): void
    {
        throw new FileNotExistsException();
    }
    private function prepare($message)
    {
        throw new RuntimeException($message);
    }
}