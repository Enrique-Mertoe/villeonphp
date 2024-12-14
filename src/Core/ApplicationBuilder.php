<?php

namespace Villeon\Core;

use Villeon\Config\ConfigBuilder;
use Villeon\Core\Facade\Facade;
use Villeon\Core\Internal\Settings;
use Villeon\Core\Rendering\RenderBuilder;
use Villeon\Core\Routing\Router;
use Villeon\Support\Admin\AdminPanel;
use Villeon\Support\ControlPanel\ControlPanel;
use Villeon\Support\AppEnvironmentVars;
use Villeon\Support\Extensions\ExtensionManager;

class ApplicationBuilder
{
    protected VilleonBuilder $app;

    public function __construct()
    {
        $this->init_components();
        $this->app = VilleonBuilder::builder();
//        $this->app->theme->initialize(BASE_PATH)
//            ->ensure_configured();
        $this->app->make_config();
    }

    private function init_components(): void
    {
        $this->init_facades();
        ExtensionManager::init();
        ControlPanel::builder();
        AdminPanel::builder();
    }

    private function init_facades(): void
    {
        Facade::setFacade("settings", new Settings());
        Facade::setFacade("config", new ConfigBuilder());
        Facade::setFacade("route", new Router("default"));
        Facade::setFacade("render", new RenderBuilder());
        Facade::setFacade("env", new AppEnvironmentVars(BASE_PATH));
    }

    protected function load_extensions(): void
    {
        ExtensionManager::load_all();
    }


}
