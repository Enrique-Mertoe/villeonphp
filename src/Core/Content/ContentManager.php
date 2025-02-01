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

/**
 * Class ContentManager
 *
 * Manages the application's core content and middleware execution.
 */
class ContentManager implements ContentMiddleWare
{
    /** @var AppCombat The application runtime environment */
    public AppCombat $appCombat;

    /** @var ContentManager Singleton instance */
    private static ContentManager $instance;

    /** @var array Middleware storage for before and after request hooks */
    private array $middleWares = [];

    /**
     * ContentManager constructor.
     *
     * Initializes the singleton instance.
     */
    public function __construct()
    {
        self::$instance = $this;
    }

    /**
     * Starts the application and resolves registered routes.
     *
     * This method triggers request handling and executes middleware if registered.
     *
     * @return void
     */
    #[NoReturn]
    public function start(): void
    {
        $this->appCombat->resolveRoutes($this->middleWares);
    }

    /**
     * Registers a callback to execute **before** request processing.
     *
     * This allows users to define middleware that runs before the request is handled.
     * A before-request function should **not return anything** unless it wants to
     * halt execution by returning a `Response` object or `String` or `Array`.
     *
     * **Example Usage:**
     * <pre>
     * $app->beforeRequest(function () {
     *     if (\Villeon\Http\Request::$endpoint !== "login") {
     *         return redirect(url_for("login"));
     *     }
     * });
     * </pre>
     *
     * @param callable $f A function that runs before request execution.
     *                    Can return a `Response` to halt execution, otherwise `null`.
     *
     */
    public function beforeRequest(callable $f): static
    {
        $this->middleWares["before"] = $f;
        return $this;
    }

    /**
     * Registers a callback to execute **after** request processing.
     *
     * This allows users to modify the response before it is sent back to the client.
     * An after-request function should return the **modified** or **unmodified** `Response` object.
     *
     * **Example Usage:**
     * <pre>
     * $app->afterRequest(function (\Villeon\Http\Response $response) {
     *     $response->headers->set("X-Framework", "Villeon");
     *     return $response;
     * });
     * </pre>
     *
     * @param callable $f A function that receives a `Response` object and must return a `Response`.
     */
    public function afterRequest(callable $f): static
    {
        $this->middleWares["after"] = $f;
        return $this;
    }

    /**
     * Loads and initializes core system components such as facades.
     *
     * @return void
     */
    protected function loadComponents(): void
    {
        $this->initFacades();
    }

    /**
     * Initializes global facades for environment, settings, configuration, and routing.
     *
     * @return void
     */
    private function initFacades(): void
    {
        Facade::setFacade("env", new AppEnvironmentVars($this->appCombat->basePath));
        Facade::setFacade("settings", new Settings($this->appCombat->basePath));
        Facade::setFacade("config", new ConfigBuilder());
        Facade::setFacade("route", new Router("default"));
    }

    /**
     * Builds the application by initializing components, extensions, and rendering.
     *
     * @return void
     */
    protected function build(): void
    {
        $this->appCombat->loadAll();
        ExtensionManager::init();
        RenderBuilder::config($this->appCombat);

        if (env("REQUIRE_PANEL")) {
            ControlPanel::builder();
        }
        AdminPanel::builder();
        ExtensionManager::load_all();

        $this->appCombat->getIncludes()
            ->map(fn($item) => $this->appCombat->basePath . "/src/" . $item . ".php")
            ->each(function ($item) {
                require_once $item;
            });
    }

    /**
     * Retrieves the application context.
     *
     * @return Context The application context instance.
     */
    public static function getContext(): Context
    {
        return self::$instance->appCombat;
    }
}
