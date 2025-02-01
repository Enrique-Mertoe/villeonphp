<?php

namespace Villeon\Core\Content;

use JetBrains\PhpStorm\NoReturn;
use Throwable;
use Villeon\Application;
use Villeon\Core\Kernel\Kernel;
use Villeon\Http\Request;
use Villeon\Http\Response;
use Villeon\Library\Collection\ImmutableList;
use Villeon\Theme\ThemeBuilder;
use Villeon\Utils\Console;

class AppCombat extends AppContext implements AppEventHandler,
    MiddleWareResolver
{
    private array $middleWares = [];

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

    #[NoReturn] public function resolveRoutes(array $middleWares): void
    {
        $this->middleWares = $middleWares;
        Kernel::resolve($this, $this, $this);
        $this->dispatchEvent($this->response);
        exit;
    }

    private function dispatchEvent(Response $response, bool $err = false): void
    {
        $this->onAfterRequest($response, $err, function (Response $response) {
            $uri = $response->uri();
            if ($response = $response->resolved()) {
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


    public function onResponse(Response $response): void
    {
        $this->dispatchEvent($response);
    }

    private function error_logger(?Throwable $error): void
    {
        log_error($error);
    }

    public function onBeforeRequest(\Closure $f): void
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

    public function onAfterRequest(Response $response, bool $ignore = false, \Closure $f = null): void
    {
        if ($ignore || !isset($this->middleWares["after"])) {
            $f($response);
            return;
        }
        if (($res = call_user_func($this->middleWares["after"], $response)) instanceof Response) {
            $f($res);
            return;
        }
        throw new \RuntimeException("Valid response required!");

    }

    public function onError(Response $response): void
    {
        $this->dispatchEvent($response, true);
    }
}
