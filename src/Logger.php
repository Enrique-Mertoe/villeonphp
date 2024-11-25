<?php

namespace Villeon;

use Villeon\Http\Request;
use Villeon\Utils\Console;

class Logger
{
    private static function rule_logger($path, int $status = 200): void
    {
        $method = Request::$method;
        $timestamp = date('Y-m-d H:i:s'); // Get current timestamp
        $logMessage = sprintf('[%s] %s %s -%s', $timestamp, $method, $path, $status);
        if ($status !== 200)
            Console::Warn($logMessage);
        else
            Console::Write($logMessage);
    }
}