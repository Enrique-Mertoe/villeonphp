<?php

namespace Villeon\Core\Internal;

use Villeon\Core\Facade\Env;

final class Settings
{
    private array $settings;
    private string $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
    }

    public function get($key, $default = null)
    {
        return env($key, $default);
    }

    private function save($key, $value): void
    {

        $this->settings[$key] = $value;
        $file = $this->basePath . "/.env";
        $out = [];
        foreach ($this->settings as $key => $value) {
            $out[] = "$key=" . $this->reverseMap($value);
        }
        file_put_contents($file, implode("\n", $out));
    }

    public function all(): array
    {
        return Env::all();
    }

    public function set(string $key, mixed $value): void
    {
        $this->save($key, $value);
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
