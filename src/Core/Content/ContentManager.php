<?php

namespace Villeon\Core\Content;

use JetBrains\PhpStorm\NoReturn;
use Villeon\Config\ConfigBuilder;
use Villeon\Core\Facade\Extension;
use Villeon\Core\Facade\Facade;
use Villeon\Core\Internal\Settings;
use Villeon\Core\Kernel\Kernel;
use Villeon\Core\Rendering\RenderBuilder;
use Villeon\Core\Routing\Router;
use Villeon\Support\Admin\AdminPanel;
use Villeon\Support\AppEnvironmentVars;
use Villeon\Support\ControlPanel\ControlPanel;
use Villeon\Support\Extensions\ExtensionManager;
use Villeon\Theme\ThemeBuilder;

class ContentManager implements ContentMiddleWare
{
    public AppCombat $appCombat;
    private static ContentManager $instance;

    public function __construct()
    {
        self::$instance = $this;
    }

    #[NoReturn]
    public function start(): void
    {
        $this->appCombat->resolveRoutes();
    }

    public function beforeRequest(callable $f): static
    {
        return $this;
    }

    public function afterRequest(callable $f): static
    {
        return $this;
    }

    protected function loadComponents(): void
    {
        $this->initFacades();

    }

    private function initFacades(): void
    {
        Facade::setFacade("env", new AppEnvironmentVars($this->appCombat->basePath));
        Facade::setFacade("settings", new Settings($this->appCombat->basePath));
        Facade::setFacade("config", new ConfigBuilder());
        Facade::setFacade("route", new Router("default"));

    }

    private function srcConfig(): void
    {
        $this->appCombat->getIncludes()
            ->map(fn($item) => $this->appCombat->basePath . "/src/" . $item . ".php")
            ->each(function ($item) {
                require_once $item;
            });
    }

    private function loadExtensions(): void
    {
        ExtensionManager::load_all();
    }

    protected function build(): void
    {
        $this->appCombat->loadAll();
        ExtensionManager::init();
        RenderBuilder::config($this->appCombat);
        if (env("REQUIRE_PANEL"))
            ControlPanel::builder();
        AdminPanel::builder();
        $this->loadExtensions();
        $this->srcConfig();

    }

    public static function getContext(): Context
    {
        return self::$instance->appCombat;
    }
}
