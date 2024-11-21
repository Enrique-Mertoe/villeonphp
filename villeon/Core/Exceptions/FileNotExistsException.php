<?php

namespace Villeon\Core\Exceptions;

use Exception;

class FileNotExistsException extends Exception
{
    public function __construct($file = "", $code = 0, Exception $previous = null)
    {
        parent::__construct("File not exists: " . $file, $code, $previous);
    }
}
