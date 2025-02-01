<?php

namespace Villeon\Core\Content;

use Closure;
use JetBrains\PhpStorm\NoReturn;
use RuntimeException;
use Throwable;
use Villeon\Application;
use Villeon\Core\Kernel\Kernel;
use Villeon\Http\Request;
use Villeon\Http\Response;
use Villeon\Library\Collection\ImmutableList;
use Villeon\Theme\ThemeBuilder;
use Villeon\Utils\Console;

/**
 * Class AppCombat
 *
 * Handles the application's lifecycle, middleware resolution, and request/response processing.
 */
class AppCombat extends AppContext implements AppEventHandler, MiddlewareHandler
{
    /** @var array Middleware handlers */
    private array $middleWares = [];

    /**
     * Initializes the application instance.
     *
     * @param Application $app The application instance.
     */
    public function initApp(Application $app): void
    {
        $this->application = $app;
        $this->request = (new Request())->build();
        $this->response = new Response;
    }

    /**
     * Loads all necessary components (e.g., themes).
     */
    public function loadAll(): void
    {
        $this->theme = new ThemeBuilder($this);
    }

    /**
     * Sets the directory for serving static files.
     *
     * @param string $path The path to the static directory.
     */
    public function setStaticDir(string $path): void
    {
        $this->staticDir = $path;
    }

    /**
     * Sets the directory for template files.
     *
     * @param string $path The path to the template directory.
     */
    public function setTemplateDir(string $path): void
    {
        $this->templateDir = $path;
    }

    /**
     * Retrieves a list of included components (e.g., views, models).
     *
     * @return ImmutableList The list of included components.
     */
    public function getIncludes(): ImmutableList
    {
        return listOf("views", "models");
    }

    /**
     * Resolves and executes application routes.
     *
     * @param array $middleWares An array of middleware functions.
     *
     * @return void
     */
    #[NoReturn]
    public function resolveRoutes(array $middleWares): void
    {
        $this->middleWares = $middleWares;
        Kernel::resolve($this, $this, $this);
        $this->dispatchEvent($this->response);
        exit;
    }

    /**
     * Dispatches an event and handles response processing.
     *
     * @param Response $response The response object.
     * @param bool $err Whether the response contains an error.
     *
     * @return void
     */
    private function dispatchEvent(Response $response, bool $err = false): void
    {
        $this->onAfterRequest($response, $err, function (Response $res) {
            $uri = $res->uri();
            if ($response = $res->resolved()) {
                $this->rule_logger($uri, $response["code"]);
                $this->error_logger($response["error"]);
                http_response_code($response["code"]);

                if (!empty($response["headers"])) {
                    $this->set_headers($response["headers"]);
                }

                if (!empty($response["location"])) {
                    header("Location: " . $response["location"]);
                    exit;
                }

                print_r($response["content"]);
            }
        });
    }

    /**
     * Sets HTTP headers for the response.
     *
     * @param array $headers An associative array of headers.
     *
     * @return void
     */
    private function set_headers(array $headers): void
    {
        foreach ($headers as $name => $value) {
            header("$name: $value");
        }
    }

    /**
     * Logs HTTP request details.
     *
     * @param string $path The request path.
     * @param int $status The HTTP status code (default: 200).
     *
     * @return void
     */
    private function rule_logger(string $path, int $status = 200): void
    {
        $method = Request::$method;
        $timestamp = date('M j Y H:i:s');

        if ($args = http_build_query(Request::$args)) {
            $path .= "?$args";
        }

        $buffer = "~[$timestamp] [$method:$status] › $path";
        Console::Write($buffer);
    }

    /**
     * Handles response dispatching.
     *
     * @param Response $response The response object.
     *
     * @return void
     */
    public function onResponse(Response $response): void
    {
        $this->dispatchEvent($response);
    }

    /**
     * Logs errors if they occur.
     *
     * @param Throwable|null $error The throwable error object.
     *
     * @return void
     */
    private function error_logger(?Throwable $error): void
    {
        log_error($error);
    }

    /**
     * Executes the "before request" middleware if defined.
     *
     * @param Closure $f The function to execute after middleware processing.
     *
     * @return void
     */
    public function onBeforeRequest(Closure $f): void
    {
        if (!isset($this->middleWares["before"])) {
            return;
        }

        if (($res = call_user_func($this->middleWares["before"])) === null) {
            $f();
        } else {
            $this->onResponse($res instanceof Response ? Response::from($res) : new Response(content: $res));
        }
    }

    /**
     * Executes the "after request" middleware if defined.
     *
     * @param Response $response The response object.
     * @param bool $ignore Whether to ignore middleware processing.
     * @param Closure|null $f The function to execute after middleware processing.
     *
     * @return void
     * @throws RuntimeException If the response is not valid.
     *
     */
    public function onAfterRequest(Response $response, bool $ignore = false, Closure $f = null): void
    {
        if ($ignore || !isset($this->middleWares["after"])) {
            $f($response);
            return;
        }

        if (($res = call_user_func($this->middleWares["after"], $response)) instanceof Response) {
            $f($res);
            return;
        }

        throw new RuntimeException("Valid response required!");
    }

    /**
     * Handles error response processing.
     *
     * @param Response $response The response object containing error details.
     *
     * @return void
     */
    public function onError(Response $response): void
    {
        $this->dispatchEvent($response, true);
    }
}
