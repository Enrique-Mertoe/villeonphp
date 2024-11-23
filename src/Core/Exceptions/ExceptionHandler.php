<?php

use Villeon\Theme\ThemeBuilder;

function ExceptionHandler(Throwable $exception): void
{
    // Log error to the console
    error_log("[ERROR] " . $exception->getMessage());
    error_log($exception->getTraceAsString());
    http_response_code(500);
//    $exception->getTrace()[0]::class;
    ThemeBuilder::$instance->display_error([
        "error" => [
            "message" => $exception->getMessage(),
            "line" => $exception->getLine(),
            "trace" => $exception->getTrace(),
            "file" => $exception->getFile(),
            "code" => $exception->getCode(),
            "class" => $exception->getTrace()[0],

        ]
    ]);
}