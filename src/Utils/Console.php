<?php

namespace Villeon\Utils;

class Console
{
    /**
     * @var false|resource
     */
    private $stream;

    public function __construct()
    {
        $this->stream = $this->openErrorStream();
    }

    public static function Write($message): void
    {
        (new Console())->doWrite($message, "white");
    }
    public static function Warn($message): void
    {
        (new Console())->doWrite($message, "yellow");
    }

    function doWrite($message, $color = null): void
    {
        if ($color) {
            $message = $this->applyColor($message, $color);
        }
        $message .= \PHP_EOL;
        @fwrite($this->stream, $message);
        fflush($this->stream);
    }

    private function openErrorStream()
    {
        if (!$this->hasStderrSupport()) {
            return fopen('php://output', 'w');
        }

        // Use STDERR when possible to prevent from opening too many file descriptors
        return \defined('STDERR') ? \STDERR : (@fopen('php://stderr', 'w') ?: fopen('php://output', 'w'));
    }

    private function isRunningOS400(): bool
    {
        $checks = [
            \function_exists('php_uname') ? php_uname('s') : '',
            getenv('OSTYPE'),
            \PHP_OS,
        ];

        return false !== stripos(implode(';', $checks), 'OS400');
    }

    protected function hasStderrSupport(): bool
    {
        return false === $this->isRunningOS400();
    }

    private function applyColor($message, $color): string
    {
        $colors = [
            'black' => '30',
            'red' => '31',
            'green' => '32',
            'yellow' => '33',
            'blue' => '34',
            'magenta' => '35',
            'cyan' => '36',
            'white' => '15',
            'bold' => '1',
            'reset' => '0',
        ];

        if (array_key_exists($color, $colors)) {
            $code = $colors[$color];
            return "\033[" . $code . "m" . $message . "\033[0m";
        }

        return $message;
    }
}