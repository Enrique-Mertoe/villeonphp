<?php

namespace Villeon\Core\Internal;

use Villeon\Core\Facade\Env;
use Villeon\Library\Collection\Collection;
use Villeon\Library\Collection\Dict;
use Villeon\Support\AppEnvironmentVars;

/**
 * Settings.php
 * @package    Villeon\Manager\Process
 * @author     Abuti Martin <abutimartin778@gmail.com>
 * @copyright  2024 Villeon
 * @license    MIT License
 * @version    1.1.0
 * @link       https://github.com/Enrique-Mertoe/villeonphp
 */
final class Settings
{
    /**
     * @var array
     */
    private array $settings;
    /**
     * @var string
     */
    private string $basePath;
    /**
     * @var Settings
     */
    private static Settings $instance;

    /**
     * @param string $basePath
     */
    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
        self::$instance = $this;
    }

    /**
     * @return Settings
     */
    public static function getInstance(): Settings
    {
        return self::$instance;
    }

    /**
     * @param $key
     * @param $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return env($key, $default);
    }

    /**
     * @param $key
     * @param $value
     * @return void
     */
    private function save($key, $value): void
    {
        $this->settings = Env::all();

        $this->settings[$key] = $value;
        $file = $this->basePath . "/.env";
        $out = [];
        foreach ($this->settings as $key => $value) {
            $str = str($key);
            if ($str->startsWith("__empty__"))
                $out[] = "";
            elseif ($str->startsWith("__comment__"))
                $out[] = $value;
            else
                $out[] = "$key=" . $this->reverseMap($value);
        }
        file_put_contents($file, implode("\n", $out));
    }

    /**
     * @return Dict
     */
    public function all(): Dict
    {
        return Env::all();
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        $this->update([$key => $value]);
    }

    /**
     * @param array $new_keys
     * @return void
     */
    public function update(array $new_keys): void
    {
        $defined = AppEnvironmentVars::getBuildVars();
        $new_keys = Dict::from($new_keys);
        $st = mutableListOf(...$defined->keys());
        foreach ($new_keys as $key => $value) {
            if ($defined->hasKey("__comment__#$key")) {
                $defined[$key] = $value;
                $st->set($st->indexOf("__comment__#$key"), $key);
                $defined->pop("__comment__#$key");
            } else
                $defined[$key] = $value;
        }
        $st->flip()->merge($defined->toArray());
        $this->makeChanges(\dict($st->toArray()));
    }

    /**
     * @param Collection $dict
     * @return void
     */
    private function makeChanges(Collection $dict): void
    {
        $file = $this->basePath . "/.env";
        $out = [];
        $dict->each(function ($key, $value) use (&$out) {
            $str = str($key);
            if ($str->startsWith("__empty__"))
                $out[] = "";
            elseif ($str->startsWith("__comment__"))
                $out[] = $str->replace("__comment__", "") . "=" . $this->reverseMap($value);
            else
                $out[] = "$key=" . $this->reverseMap($value);

        });
        file_put_contents($file, implode("\n", $out));
    }

    /**
     * @param mixed $value
     * @return string
     */
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
