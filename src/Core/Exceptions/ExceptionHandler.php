<?php

use Villeon\Theme\ThemeBuilder;

/**
 * @param Throwable $exception
 * @return void
 * @throws \Twig\Error\LoaderError
 * @throws \Twig\Error\RuntimeError
 * @throws \Twig\Error\SyntaxError
 */
function ExceptionHandler(Throwable $exception): void
{

    error_log("[ERROR] " . $exception->getMessage());
    error_log($exception->getTraceAsString());
    http_response_code(500);
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