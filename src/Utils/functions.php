<?php
if (!function_exists("php_executor")) {
    function php_executor(): string
    {
        if (PHP_BINARY && in_array(PHP_SAPI, ['cli', 'cli-server', 'phpdbg'], true)) {
            return PHP_BINARY;
        }
        if (@is_executable($php = PHP_BINDIR . ('\\' === DIRECTORY_SEPARATOR ? '\\php.exe' : '/php')) && !@is_dir($php)) {
            return $php;
        }
        throw new RuntimeException("No php executable!");
    }
}
