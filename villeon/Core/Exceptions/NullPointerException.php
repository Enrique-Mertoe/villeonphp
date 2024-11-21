<?php

namespace Villeon\Core\Exceptions;

use Exception;

class NullPointerException extends Exception
{
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct("Null pointer exception occurred: " . $message, $code, $previous);
    }
}
