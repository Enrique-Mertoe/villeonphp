<?php

namespace Villeon\Core\Exceptions;

use Exception;

class UnImplementedException extends Exception
{
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct("Not implemented " . $message, $code, $previous);
    }
}
