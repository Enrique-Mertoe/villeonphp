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
        if (!empty(array_diff($keys, array_keys($this->loaded_vars)))) {
            return [];
        }
        return array_intersect_key($this->loaded_vars, array_flip($keys));
    }

    private function load_vars(): void
    {
        $file = $this->base_path . "/.env";
        if (!file_exists($file))
            $this->loaded_vars = [];
        $content = explode("\n", file_get_contents($file));

        foreach ($content as $line) {
            $line = $this->normalize_var($line);
            if ($line) {
                $seg = preg_split('/\s*+=\s*/', $line);
                $this->process_segments($seg);
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

        $this->loaded_vars[$key] = $value;
    }

    private function map($value)
    {
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return;
        }
        if (preg_match('/\A([\'"])(.*)\1\z/', $value, $matches)) {
            return $matches[2];
        }
        return $value;
    }
}