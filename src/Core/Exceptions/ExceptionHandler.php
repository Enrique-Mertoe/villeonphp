<?php

use JetBrains\PhpStorm\NoReturn;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Villeon\Http\Request;
use Villeon\Theme\ThemeBuilder;
use Villeon\Utils\Console;

/**
 * @param Throwable $exception
 * @return void
 * @throws LoaderError
 * @throws RuntimeError
 * @throws SyntaxError
 */
#[NoReturn] function ExceptionHandler(Throwable $exception): void
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
    exit;
}
