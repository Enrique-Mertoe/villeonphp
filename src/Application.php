<?php

namespace Villeon;


/**
 * @author SmallVilleCycle
 * @author_email smallvillecycle5@gmail.com
 *  +--------------------------------------------+
 *  Copyright (c) 2024 SmallVille. All rights reserved.
 */


use Villeon\Config\ConfigBuilder;
use Villeon\Core\ApplicationBuilder;
use Villeon\Core\Facade\Config;
use Villeon\Core\VilleonBuilder;
use Villeon\Database\VilleonSQL\Connection\DBOptions;
use Villeon\Database\VilleonSQL\VilleonSQL;


final class Application extends ApplicationBuilder
{
    /**
     * @return void
     */
    public function create(): void
    {
        VilleonSQL::init_database(
            new DBOptions(
                host: env("DB_SERVER", ''),
                user: env("DB_USER", ''),
                password: env("DB_PASSWORD", ''),
                name: env("DB_NAME", '')
            )
        );
        (new VilleonSQL())->build();
        $this->load_extensions();
        $this->app->build();

    }

    public function withConfig(callable $callback): Application
    {
        $callback();
        Config::load_module("views");
//        Config::load_module("models");
        return $this;
    }
}
