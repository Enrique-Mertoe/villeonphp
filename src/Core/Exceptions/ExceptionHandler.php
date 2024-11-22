<?php

use Villeon\Theme\ThemeBuilder;

function ExceptionHandler(Throwable $exception): void
{
    // Log error to the console
    error_log("[ERROR] " . $exception->getMessage());
    error_log($exception->getTraceAsString());

    // Render a readable HTML page for errors
    http_response_code(500); // Set HTTP status code
//    echo "<html>
//        <head><title>Error</title></head>
//        <body style='font-family: Arial, sans-serif;'>
//            <h1>An Error Occurred</h1>
//            <p><strong>Message:</strong> {$exception->getMessage()}</p>
//            <p><strong>File:</strong> {$exception->getFile()}</p>
//            <p><strong>Line:</strong> {$exception->getLine()}</p>
//            <pre><strong>Trace:</strong>\n{$exception->getTraceAsString()}</pre>
//        </body>
//    </html>";
    ThemeBuilder::$instance->display_error([
        "error" => [
            "message" => $exception->getMessage(),
            "line" => $exception->getLine(),
            "trace" => $exception->getTraceAsString(),
            "file" => $exception->getFile(),
            "code"=>$exception->getCode()
        ]
    ]);
}