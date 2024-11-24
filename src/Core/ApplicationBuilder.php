<?php

namespace Villeon\Core;

use Villeon\Config\ConfigBuilder;
use Villeon\Core\Facade\Facade;
use Villeon\Core\Rendering\RenderBuilder;
use Villeon\Core\Routing\Router;
use Villeon\Support\ControlPanel\ControlPanel;
use Villeon\Support\Extensions\ExtensionBuilder;
use Villeon\Support\Extensions\ExtensionManager;

class ApplicationBuilder
{
    public function __construct()
    {
        $this->init_components();
    }

    private function init_components(): void
    {
        $this->init_facades();
        ExtensionManager::init();
        ControlPanel::builder();
    }

    private function init_facades(): void
    {
        Facade::setInstance("config", new ConfigBuilder());
        Facade::setInstance("route", new Router("default"));
        Facade::setInstance("render", new RenderBuilder());
    }

    protected function load_extensions(): void
    {
        ExtensionManager::load_all();
    }
}