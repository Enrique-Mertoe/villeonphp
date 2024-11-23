<?php

namespace Villeon;


/**
 * @author SmallVilleCycle
 * @author_email smallvillecycle5@gmail.com
 *  +--------------------------------------------+
 *  Copyright (c) 2024 SmallVille. All rights reserved.
 */


use Villeon\Config\ConfigBuilder;
use Villeon\Core\Facade\Config;
use Villeon\Core\Facade\Facade;
use Villeon\Core\Rendering\RenderBuilder;
use Villeon\Core\Routing\Router;
use Villeon\Core\VilleonBuilder;
use Villeon\Database\VilleonSQL\Connection\DBOptions;
use Villeon\Database\VilleonSQL\VilleonSQL;


class Application
{
    /**
     * @return void
     */
    public function create(): void
    {
        VilleonSQL::init_database(
            new DBOptions(
                host: DATABASE_CONFIG["host"],
                user: DATABASE_CONFIG["user"],
                password: DATABASE_CONFIG["password"],
                name: DATABASE_CONFIG["name"]
            )
        );
        (new VilleonSQL())->build();
        $app = VilleonBuilder::builder();
        $app->theme->initialize(BASE_PATH);
        $app->build();

    }

    public function withConfig(callable $callback): static
    {
        Facade::setInstance("config", new ConfigBuilder());
        Facade::setInstance("route", new Router());
        Facade::setInstance("render", new RenderBuilder());
        $callback();
        Config::load_module("views");
        Config::load_module("models");
        return $this;
    }
}
