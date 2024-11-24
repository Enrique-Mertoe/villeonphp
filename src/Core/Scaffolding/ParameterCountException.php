<?php

namespace Villeon\Core\Scaffolding;

use RuntimeException;

class ParameterCountException extends RuntimeException
{
    public function __construct($required, $found)
    {
        $message = "Parameter count mismatch: Expected $required, but got $found.";
        if ($required < $found) {
            $message = "Too many parameters passed to the controller. Expected $required, got $found.";
        }
        if ($required > $found) {
            $message = "Not enough parameters passed to the controller. Expected $required, got $found.";
        }
        parent::__construct($message);
    }
}