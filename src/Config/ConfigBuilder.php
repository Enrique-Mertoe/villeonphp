<?php

namespace Villeon\Config;

use Villeon\Core\Facade\Env;

class ConfigBuilder
{
    private string $SRC_DIR;
    private array $modules = [];


    /**
     * @param string $name
     * @return ConfigBuilder
     */
    public function load_module(string $name): ConfigBuilder
    {
        $this->modules[] = $name;
        return $this;
    }

    /**
     * @param string $name
     * @return ConfigBuilder
     */
    public function set_src(string $name): ConfigBuilder
    {
        $this->SRC_DIR = $name;
        return $this;
    }

    public function merge_imported(): void
    {
        foreach ($this->modules as $module) {
            require $this->SRC_DIR . "/$module.php";
        }
    }

    public function db_info(): array
    {
        return [
            "DB_SERVER" => Env::get("DB_SERVER"),
            "DB_NAME" => Env::get("DB_NAME"),
            "DB_PASSWORD" => Env::get("DB_PASSWORD"),
            "DB_USER" => Env::get("DB_USER"),
        ];
    }
}
