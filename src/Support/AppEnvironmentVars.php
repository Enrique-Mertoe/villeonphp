<?php

namespace Villeon\Support;


use Villeon\Library\Collection\ChainMap;
use Villeon\Library\Collection\Dict;

/**
 * APPEnvironmentVars.php
 * @package    Villeon\Library\Collection\Dict
 * @author     Abuti Martin <abutimartin778@gmail.com>
 * @copyright  2024 Villeon
 * @license    MIT License
 * @version    1.1.0
 * @link       https://github.com/Enrique-Mertoe/villeonphp
 */
final class AppEnvironmentVars
{
    /**
     * @var string
     */
    private string $base_path;
    /**
     * @var Dict
     */
    private Dict $loaded_vars;
    /**
     * @var ChainMap $envVars
     */
    private ChainMap $envVars;
    /**
     * @var AppEnvironmentVars
     */
    private static AppEnvironmentVars $instance;


    /**
     * @param string $base_path
     */
    public function __construct(string $base_path)
    {
        $this->base_path = $base_path;
        $this->loaded_vars = \dict();
        self::$instance = $this;
        $this->load_vars();
    }

    /**
     * @return Dict
     */
    public static function getBuildVars(): Dict
    {
        return self::$instance->loaded_vars;
    }

    /**
     * @param $key
     * @param $default
     * @return mixed|null
     */
    public function get($key, $default = null): mixed
    {
        return $this->envVars->get($key, $default);
    }

    /**
     * @return Dict
     */
    public function all(): Dict
    {
        return $this->loaded_vars;
    }

    /**
     * @param ...$keys
     * @return bool
     */
    public function has(...$keys): bool
    {
        return $this->loaded_vars->hasKey(...$keys);
    }

    /**
     * @return void
     */
    private function load_vars(): void
    {
        $files = [$this->base_path . "/.env", $this->base_path . "/.env.local"];
        foreach ($files as $file) {
            if (file_exists($file)) {
                $this->parse_file($file);
            }
        }
        $this->envVars = new ChainMap($this->loaded_vars->toArray(), $_ENV);
    }

    /**
     * @param string $file
     * @return void
     */
    private function parse_file(string $file): void
    {
        $content = file_get_contents($file);
        $lines = explode("\n", $content);
        foreach ($lines as $index => $line) {
            $line = $this->normalize_var($line);
            if ($line) {
                $segments = preg_split('/\s*+=\s*/', $line, 2);
                $res = $this->process_segments($segments);
                if ($res) {
                    $prefix = preg_match('/^[;#]/', $line) ? "__comment__" : "";
                    $this->loaded_vars[$prefix . $res[0]] = $res[1];
                }

            } else {
                $this->loaded_vars["__empty__" . ($index + 1)] = $index + 1;
            }
        }
    }

    /**
     * @param $var
     * @return string|null
     */
    private function normalize_var($var): ?string
    {
        $var = trim($var);
        return $var ?: null;
    }

    /**
     * @param array $segments
     * @return array
     */
    private function process_segments(array $segments): array
    {
        if (!$segments) return [];
        $key = $segments[0] ?? null;
        $value = $this->map($segments[1] ?? '');

        if (preg_match_all('/\${(.*?)}/', $value, $matches)) {
            foreach ($matches[1] as $match) {
                if (isset($this->loaded_vars[$match])) {
                    $value = str_replace('${' . $match . '}', $this->loaded_vars[$match], $value);
                }
            }
        }
        return [$key, $value];
    }

    /**
     * @param $value
     * @return bool|float|int|mixed|string|null
     */
    private function map($value): mixed
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
