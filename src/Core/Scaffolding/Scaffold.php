<?php

namespace Villeon\Core\Scaffolding;

use Exception;
use JetBrains\PhpStorm\NoReturn;
use ReflectionException;
use ReflectionFunction;
use RuntimeException;
use Throwable;
use Villeon\Core\Routing\Route;
use Villeon\Core\Routing\RouteRegistry;
use Villeon\Error\RuntimeError;
use Villeon\Http\Request;
use Villeon\Http\Response;
use Villeon\Theme\ThemeBuilder;
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
     */
    protected function init_routes(): void
    {


        $this->blue_prints = RouteRegistry::build();
        $routes = $this->blue_prints;
        $default = $routes["default"];
        unset($routes["default"]);

        foreach ($this->prepare_routes($default->get_defined_routes()->getAll()) as $route) {
            $this->process_route($route);
        }
        foreach ($routes as $name => $group) {
            foreach ($this->prepare_routes($group->get_defined_routes()->getAll()) as $route) {
                if ($this->process_route($route)) {
                    return;
                }
            }
        }
        $this->manage_defined_blue_prints();
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
            try {
                $match = $match[1];
                if ($route->required_params && ($defined = $this->isDefined(array_slice($match, count($route->required_params) - 1)))) {
                    $this->dispatch($defined);
                    exit();
                }
                $this->dispatch($route, $match);
                return true;
            }catch (Throwable $e){
                throw new RuntimeError($e->getMessage());
            }

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
            foreach ($this->prepare_routes($group->get_defined_routes()->getAll()) as $route) {
                if ($route->rule === "/" . $v)
                    return $route;
            }
        }
        return null;
    }

    /**
     * If no matching url, check the current uri with the url prefix of defined blueprints
     * then redirect to its index page
     * @return void
     */
    private function manage_defined_blue_prints(): void
    {
        $uri = Request::$uri;
        if (RouteRegistry::get_by_prefix($uri)) {
            header("Location: $uri/");
            exit;
        }
    }

    /**
     * @param Route $route
     * @param array $args
     * @return void
     * @throws Throwable
     */
    protected function dispatch(Route $route, array $args = []): void
    {

        $controller = $route->controller;
        try {
            $reflection = new ReflectionFunction($controller);
        } catch (ReflectionException $e) {
            throw new RuntimeException($e);
        }
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
            if ($res instanceof Throwable) {
                throw $res;
            }
            if ($res instanceof Response)
                $this->commit(Response::from($res));

            if (is_string($res)) {
                $content = $res;
            } elseif (is_array($res))
                $content = json_encode($res);
            else {
                throw new RuntimeException("View function did not return valid response: found " .
                    gettype($res));
            }
            $this->commit(new Response($content));


        }
        $this->commit(new Response);
    }

    #[NoReturn] private function commit(Response $response): void
    {
        if ($response = $response->resolved()) {
            http_response_code($response["code"]);
            if (!empty($response["headers"]))
                $this->set_headers($response["headers"]);
            if (!empty($response["location"]))
                header("Location: " . $response["location"]);
            print_r($response["content"]);
            exit;
        }
    }

    private function set_headers(array $headers): void
    {
        foreach ($headers as $name => $value) {
            header("$name: $value");
        }
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
     * @return Route|null
     */
    private function is404Defined(): ?Route
    {
        return $this->blue_prints["default"]->getErrorHandlerBluePrint()->get404();
    }

    /**
     * @return void
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
