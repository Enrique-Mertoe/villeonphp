<?php

namespace Villeon\Core\Scaffolding;

use Exception;
use JetBrains\PhpStorm\NoReturn;
use ReflectionException;
use ReflectionFunction;
use RuntimeException;
use Villeon\Core\Routing\Route;
use Villeon\Core\Routing\Router;
use Villeon\Core\Routing\RouteRegistry;
use Villeon\Http\Request;
use Villeon\Http\Response;
use Villeon\Theme\ThemeBuilder;
use Villeon\Utils\Collection;
use Villeon\Utils\Console;

class Scaffold
{
    /**
     * @var RouteRegistry[]
     */
    private array $blue_prints;

    private string $endpoint;

    /**
     * @return void
     * @throws Exception|\Throwable
     */
    protected function init_routes(): void
    {


        $this->blue_prints = RouteRegistry::build();
        $routes = $this->blue_prints;
        $default = $routes["default"];
        unset($routes["default"]);

        foreach ($this->prepare_routes($default->getBluePrints()->getAll()) as $route) {
            $this->process_route($route);
        }
        foreach ($routes as $name => $group) {
            foreach ($this->prepare_routes($group->getBluePrints()->getAll()) as $route) {
                if ($this->process_route($route)) {
                    return;
                }
            }
        }
        $this->manage_unknown_request();
    }

    /**
     * @param array $routes
     * @return array
     */
    private function prepare_routes(array $routes): array
    {
        usort($routes, function ($a, $b) {
            return $this->routePriority($b->rule) <=> $this->routePriority($a->rule);
        });
        return $routes;
    }

    private function routePriority($route): int|string
    {
        $segments = explode('/', trim($route, '/'));
        $priority = 0;
        $lastSegment = end($segments);
        if (preg_match('/\{(\w+):path}/', $lastSegment)) {
            $priority += 1000;
        }
        if (preg_match('/\{(\w+):all}/', $lastSegment)) {
            $priority += 1;
        }

        foreach ($segments as $index => $segment) {
            if (!preg_match('/\{(\w+)}/', $segment)) {
                $priority += 10 ** (count($segments) - $index);
            }
        }
        $priority = max(0, $priority - count($segments));
        return $priority - count($segments);
    }

    private function process_route(Route $route): bool
    {
        $match = $route->match(Request::$uri);
        if ($match[0]) {
            $match = $match[1];
            if ($route->required_params && ($defined = $this->isDefined(array_slice($match, count($route->required_params) - 1)))) {
                $this->dispatch($defined);
                exit();
            }
            $this->dispatch($route, $match);
            return true;

        }
        return false;
    }

    /**
     * @param $v
     * @return Route|null
     */
    private function isDefined($v): ?Route
    {
        $v = implode("/", $v);
        foreach ($this->blue_prints as $name => $group) {
            foreach ($this->prepare_routes($group->getBluePrints()->getAll()) as $route) {
                if ($route->rule === "/" . $v)
                    return $route;
            }
        }
        return null;
    }

    /**
     * @param Route $route
     * @param array $args
     * @return void
     * @throws ReflectionException
     * @throws \Throwable
     */
    protected function dispatch(Route $route, array $args = []): void
    {

        $controller = $route->controller;
        $reflection = new ReflectionFunction($controller);
        $reflectionParams = $reflection->getParameters();
        $required = count($reflectionParams);
        $found = count($args);
        if ($required != $found) {
            throw new ParameterCountException($required, $found);
        }
        $defined = $route->required_params;
        foreach ($reflectionParams as $param) {
            $expectedName = $param->getName();
            if (!array_key_exists($expectedName, $defined)) {
                throw new RuntimeException("Missing parameter: $expectedName");
            }
        }
        if (is_callable($route->controller)) {
            ob_start();
            try {
                $res = call_user_func_array($controller, $args);
            } catch (Exception $e) {
                http_response_code(500);
                $res = $e;
            }
            $bufferedOutput = ob_get_contents();
            ob_end_clean();

            Console::Write($bufferedOutput);
            $this->rule_logger(Request::$uri, http_response_code());
            if ($res instanceof \Throwable) {
                throw $res;
            }

            if (is_string($res))
                print_r($res);
            elseif (is_array($res))
                print_r(json_encode($res));
            else {
                throw new \Exception("View function did not return valid response: found " .
                    gettype($res));
            }


        }
        $this->commit(new Response);
    }

    #[NoReturn] private function commit(Response $response): void
    {
//        $response->send();
        exit();
    }

    /**
     * @param $path
     * @param int $status
     * @return void
     */
    private function rule_logger($path, int $status = 200): void
    {
        $method = Request::$method;
        $timestamp = date('Y-m-d H:i:s'); // Get current timestamp
        $logMessage = sprintf('[%s] %s %s -%s', $timestamp, $method, $path, $status);
        if ($status !== 200)
            Console::Warn($logMessage);
        else
            Console::Write($logMessage);
    }

    /**
     * @return void
     */
    private function process_endpoint(): void
    {
        $fullUri = urldecode($_SERVER['REQUEST_URI']);
        $fullUri = parse_url($fullUri)["path"];

        $fullUri = preg_replace('#/+#', '/', $fullUri);
        $fullUri = trim($fullUri);
        if (!str_starts_with($fullUri, "/"))
            $fullUri = "/$fullUri";
        $this->endpoint = empty($fullUri) ? '/' : $fullUri;
    }

    /**
     * @return Route|null
     */
    private function is404Defined(): ?Route
    {
        return $this->blue_prints["default"]->getErrorHandlerBluePrint()->get404();
    }

    /**
     * @return void
     * @throws Exception|\Throwable
     */
    private function manage_unknown_request(): void
    {
//        http_response_code(404);
        if ($route = $this->is404Defined()) {
            $this->dispatch($route);
        } else {
            ThemeBuilder::$instance->display_404();
        }

    }

    /**
     * @param int $code
     * @return void
     */
    #[NoReturn] private static function handleError(int $code): void
    {
        exit("method not allowed");
    }


}
