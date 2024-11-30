<?php

namespace Villeon\Core\Internal;

final class Settings
{
    private array $settings;

    public function __construct()
    {
        $this->load();
    }

    private function load(): void
    {
        $file = __DIR__ . "/.cong";
        if (file_exists($file)) {
            $this->parse_file($file);
        }
    }

    public function get($key, $default = null)
    {

        return $this->settings[$key] ?? $default;
    }

    private function save($key, $value): void
    {
        $this->settings[$key] = $value;
        $file = __DIR__ . "/.cong";
        $out = [];
        foreach ($this->settings as $key => $value) {
            $out[] = "$key=" . $this->reverseMap($value);
        }
        file_put_contents($file, implode("\n", $out));
    }

    public function all(): array
    {
        return $this->settings;
    }

    public function set(string $key, mixed $value): void
    {
        $this->save($key, $value);
    }

    private function parse_file(string $file): void
    {
        $content = file_get_contents($file);
        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            $line = $this->normalize_var($line);
            if ($line) {
                $segments = preg_split('/\s*+=\s*/', $line);
                $this->process_segments($segments);
            }
        }

    }

    private function normalize_var(string $line): ?string
    {
        $var = trim($line);
        if (!$var || preg_match('/^[;#]/', $var)) return null;
        return $var;
    }

    private function process_segments(array|bool $segments): void
    {
        if (!$segments) return;
        $key = $segments[0] ?? null;
        $value = $this->map($segments[1] ?? '');
        $this->settings[$key] = $value;
    }

    private function map(mixed $value)
    {
        if (preg_match('/\A([\'"])(.*)\1\z/', $value, $matches)) {
            return $matches[2];
        }

        if (strtolower($value) === 'true' || strtolower($value) === '(true)') {
            return true;
        }

        if (strtolower($value) === 'false' || strtolower($value) === '(false)') {
            return false;
        }

        if (strtolower($value) === 'null' || strtolower($value) === '(null)') {
            return null;
        }

        if (is_numeric($value)) {
            return $value + 0;
        }

        return $value;
    }

    private function reverseMap(mixed $value): string
    {
        if (is_string($value)) {
            return "\"" . $value . "\"";
        }

        if ($value === true) {
            return 'true';
        }

        if ($value === false) {
            return 'false';
        }

        if ($value === null) {
            return 'null';
        }

        if (is_numeric($value)) {
            return (string)$value;
        }
        return (string)$value;
    }
}