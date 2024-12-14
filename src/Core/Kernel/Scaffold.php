<?php

namespace Villeon\Core\Kernel;

use Villeon\Core\Content\AppContext;
use Villeon\Core\Routing\Route;
use Villeon\Core\Routing\RouteRegistry;
use Villeon\Core\Scaffolding\ParameterCountException;
use Villeon\Error\RuntimeError;
use Villeon\Http\Request;
use Villeon\Http\Response;
use Villeon\Library\Collection\Collection;
use Villeon\Library\Collection\Dict;
use Villeon\Theme\ThemeBuilder;
use Villeon\Utils\Console;

abstract class Scaffold implements EventDispatcher
{
    protected AppContext $context;
    private Dict $registries;

    protected function launch(): void
    {
        $this->registries = Dict::from(RouteRegistry::build());
        $registries = clone $this->registries;
        $this->manage_defined();
        $default = listOf(...$this->sortRoutes($registries->pop("default")
            ->get_defined_routes()
            ->getAll()));
        if ($this->handleRouteGroup($default))
            return;
        foreach ($registries as $group) {
            $group = listOf(...$this->sortRoutes($group->get_defined_routes()->getAll()));
            if ($this->handleRouteGroup($group))
                return;
        }
        $this->manageUnknown();
    }

    /**
     * @param Collection $routes
     * @return bool
     */
    private function handleRouteGroup(Collection $routes): bool
    {

        foreach ($routes as $route) {
            if ($match = $this->matchRoute($route)) {
                $this->handleRoute($match);
                return true;
            }
        }
        return false;
    }

    private function handleRoute(Collection $match): void
    {
        [$route, $info] = $match->toArray();
        if (!$route->method_allowed()) {
            $this->onFail(405, "");
            return;
        }

        if ($route->required_params && ($defined = $this->isDefined(array_slice($info, count($route->required_params) - 1)))) {
            $this->dispatch($defined);
            return;
        }
        $this->dispatch($route, $info);
    }

    protected function dispatch(Route $route, array $args = []): mixed
    {

        $controller = $route->controller;
        try {
            $reflection = new \ReflectionFunction($controller);
        } catch (\ReflectionException $e) {
            throw new \RuntimeException($e);
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
                throw new \RuntimeException("Missing parameter: $expectedName");
            }
        }
        if (is_callable($route->controller)) {
            ob_start();
            try {
                $res = call_user_func_array($controller, $args);
            } catch (\Throwable $e) {
                $res = $e;
            }
            $bufferedOutput = str(ob_get_contents());
            ob_end_clean();
            $bufferedOutput->replace("\n", "__smv__");
            if (!$bufferedOutput->empty())
                Console::Write("[USER_OUT]" . $bufferedOutput);
            if ($res instanceof \Throwable)
                throw $res;


            if ($res instanceof Response)
                return $this->onResponse(Response::from($res));

            if (is_string($res)) {
                $content = $res;
            } elseif (is_array($res))
                $content = json_encode($res);
            else {
                throw new \RuntimeException("View function did not return valid response: found " .
                    gettype($res));
            }
            return $this->onSuccess($content);
        }
        return $this->onResponse(new Response);
    }

    private function matchRoute(Route $route): ?Collection
    {

        $match = $route->match(Request::$uri);
        if ($match[0]) {
            return \listOf($route, $match[1]);
        }
        return null;
    }

    private function manage_defined(): void
    {
        $uri = Request::$uri;
        if (RouteRegistry::get_by_prefix($uri)) {
            Dispatcher::redirect("$uri/");
        }
    }

    private function sortRoutes(array $routes): array
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

    private function isDefined($v): ?Route
    {
        $v = implode("/", $v);
        foreach ($this->registries as $group) {
            foreach ($this->sortRoutes($group->get_defined_routes()->getAll()) as $route) {
                if ($route->rule === "/" . $v)
                    return $route;
            }
        }
        return null;
    }

    private function manageUnknown(): void
    {
        if ($route = $this->is404Defined()) {
            $this->dispatch($route);
        } else {
            $this->onFail(404, $this->context->getErrorContent(404));
        }
    }

    private function is404Defined(): ?Route
    {
        return $this->registries["default"]->getErrorHandlerBluePrint()->get404();
    }
}
