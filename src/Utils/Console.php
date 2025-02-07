<?php
/**
 * Console.php
 *
 * This file contains the implementation of the ServerCommand class,
 * which manages a PHP development server process and handles console output.
 *
 * @package    Villeon\Utils
 * @author     Abuti Martin <abutimartin778@gmail.com>
 * @copyright  2024 Villeon
 * @license    MIT License
 * @version    1.1.0
 * @link       https://github.com/Enrique-Mertoe/villeonphp
 */

namespace Villeon\Utils;

use function defined;
use function function_exists;
use const PHP_EOL;
use const PHP_OS;
use const STDERR;

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
        (new Console)->doWrite($message, "white");
    }

    public static function Info($message): void
    {
        (new Console)->doWrite($message, "info");
    }

    public static function Warn($message): void
    {
        (new Console)->doWrite($message, "yellow");
    }

    public static function Success($message): void
    {
        (new Console())->doWrite($message, "green");
    }

    public static function Error($message): void
    {
        (new Console())->doWrite($message, "red");
    }

    function doWrite($message, $color = null): void
    {
        if (is_array($message)) {
            $message = json_encode($message);
        }
        $message = $this->formatMessage($message);
        if ($color) {
            $message = $this->applyColor($message, $color);
        }
        $message .= PHP_EOL;
        @fwrite($this->stream, $message);
        fflush($this->stream);
    }

    private function formatMessage($input): array|string|null
    {
        $input = preg_replace_callback('/<i>(.*?)<\/i>/', function ($matches) {
            return "\033[3m" . $matches[1] . "\033[0m";
        }, $input);
        return preg_replace_callback('/<b>(.*?)<\/b>/', function ($matches) {
            return "\033[1m" . $matches[1] . "\033[0m";
        }, $input);
    }

    private function openErrorStream()
    {
        if (!$this->hasStderrSupport()) {
            return fopen('php://output', 'w');
        }
        return defined('STDERR') ? STDERR : (@fopen('php://stderr', 'w') ?: fopen('php://output', 'w'));
    }

    private function isRunningOS400(): bool
    {
        $checks = [
            function_exists('php_uname') ? php_uname('s') : '',
            getenv('OSTYPE'),
            PHP_OS,
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
            'info' => '34'
        ];

        if (array_key_exists($color, $colors)) {
            $code = $colors[$color];
            return "\033[" . $code . "m" . $message . "\033[0m";
        }

        return $message;
    }
}
