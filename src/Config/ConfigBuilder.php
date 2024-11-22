<?php

namespace Villeon\Config;

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
}