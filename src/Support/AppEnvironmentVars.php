<?php

namespace Villeon\Support;

final class AppEnvironmentVars
{
    private string $base_path;
    private array $loaded_vars;

    public function __construct(string $base_path)
    {
        $this->base_path = $base_path;
        $this->load_vars();
    }

    function get($key, $default = null)
    {
        return $this->loaded_vars[$key] ?? $default;
    }

    function has(...$keys): array
    {
        return array_intersect_key($this->loaded_vars, array_flip($keys));
    }

    private function load_vars(): void
    {
        $this->loaded_vars = [];
        $files = [$this->base_path . "/.env", $this->base_path . "/.env.local"];

        foreach ($files as $file) {
            if (file_exists($file)) {
                $this->parse_file($file);
            }
        }
        $this->loaded_vars = array_merge($_ENV, $this->loaded_vars);
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

    private function normalize_var($var): ?string
    {
        $var = trim($var);
        if (!$var || preg_match('/^[;#]/', $var)) return null;
        return $var;
    }

    private function process_segments(array $segments): void
    {
        if (!$segments) return;
        $key = $segments[0] ?? null;
        $value = $this->map($segments[1] ?? '');

        if (preg_match_all('/\${(.*?)}/', $value, $matches)) {
            foreach ($matches[1] as $match) {
                if (isset($this->loaded_vars[$match])) {
                    $value = str_replace('${' . $match . '}', $this->loaded_vars[$match], $value);
                }
            }
        }

        $this->loaded_vars[$key] = $value;
    }

    private function map($value)
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
}