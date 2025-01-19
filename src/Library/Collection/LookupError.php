<?php

namespace Villeon\Library\Collection;
use Exception;
use Throwable;

class LookupError extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
