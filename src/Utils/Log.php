<?php

namespace Villeon\Utils;

/**
 *
 */
class Log
{
    /**
     * @param string $tag
     * @param string|null $message
     * @return void
     */
    public static function i(string $tag, ?string $message): void
    {
        Console::Info(self::formatMessage($tag, $message));
    }

    /**
     * @param string $tag
     * @param string|null $message
     * @return void
     */
    public static function e(string $tag, ?string $message): void
    {
        Console::Error(self::formatMessage($tag, $message));
    }

    /**
     * @param string $tag
     * @param string|null $message
     * @return void
     */
    public static function w(string $tag, ?string $message): void
    {
        Console::Warn(self::formatMessage($tag, $message));
    }

    /**
     * @param string $tag
     * @param string $message
     * @return void
     */
    public static function d(string $tag, string $message): void
    {
        Console::Write(self::formatMessage($tag, $message));
    }

    private static function formatMessage(string $tag, string $message = ''): string
    {
        $lines = explode("\n", $message);
        $formattedMessage = sprintf("%-15s:%s", $tag, array_shift($lines));

        foreach ($lines as $line) {
            $formattedMessage .= "\n" . str_repeat(' ', 16) . $line;
        }
        return $formattedMessage;
    }

    private static function makeLinksClickable(string $message, string $baseUrl = 'vscode://file'): string
    {
        // Match file paths with line numbers (e.g., /path/to/file.php(123))
        $regex = '~(/[\w\-.\/]+\.php)\((\d+)\)~';

        // Replace with clickable links
        return preg_replace_callback($regex, function ($matches) use ($baseUrl) {
            $file = $matches[1];
            $line = $matches[2];
            // Construct clickable link (adjust based on your IDE or tool)
            $url = sprintf('%s%s:%s', rtrim($baseUrl, '/'), $file, $line);
            return sprintf('<a href="%s">%s(%d)</a>', htmlspecialchars($url), htmlspecialchars($file), $line);
        }, $message);
    }
}
