<?php

namespace Villeon\Core\Content;

use JetBrains\PhpStorm\NoReturn;
use Villeon\Application;
use Villeon\Core\Kernel\Kernel;
use Villeon\Http\Request;
use Villeon\Http\Response;
use Villeon\Library\Collection\ImmutableList;
use Villeon\Theme\ThemeBuilder;
use Villeon\Utils\Console;

class AppCombat extends AppContext implements AppEventHandler
{
    public function initApp(Application $app): void
    {
        $this->application = $app;
        $this->request = (new Request())->build();
        $this->response = new Response;
    }

    function loadAll(): void
    {
        $this->theme = new ThemeBuilder($this);
    }

    public function setStaticDir(string $path): void
    {
        $this->staticDir = $path;
    }

    public function setTemplateDir(string $path): void
    {
        $this->templateDir = $path;
    }

    public function getIncludes(): ImmutableList
    {
        return listOf("views", "models");
    }

    #[NoReturn] public function resolveRoutes(): void
    {
        Kernel::resolve($this, $this);
        $this->dispatchEvent($this->response);
        exit;
    }

    private function dispatchEvent(Response $response): void
    {
        $uri = $response->uri();
        if ($response = $response->resolved()) {
            $this->rule_logger($uri, $response["code"]);
            $this->error_logger($response["error"]);
            http_response_code($response["code"]);
            if (!empty($response["headers"]))
                $this->set_headers($response["headers"]);
            if (!empty($response["location"]))
                header("Location: " . $response["location"]);
            print_r($response["content"]);
        }

    }

    private function set_headers(array $headers): void
    {
        foreach ($headers as $name => $value) {
            header("$name: $value");
        }
    }

    private function rule_logger($path, int $status = 200): void
    {
        $method = Request::$method;
        $timestamp = date('M j Y H:i:s');
        if ($args = http_build_query(Request::$args))
            $path .= "?$args";
        $buffer = "~[$timestamp] [$method:$status] › $path";
        Console::Write($buffer);
    }


    function onResponse(Response $response): void
    {
        $this->dispatchEvent($response);
    }

    private function error_logger(?\Throwable $error): void
    {
        log_error($error);
    }
}
