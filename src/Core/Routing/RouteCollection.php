<?php

namespace Villeon\Core\Routing;

use Villeon\Error\RuntimeError;

class RouteCollection
{
    /**
     * @var Route[]
     */
    private array $routes = [];

    public function add(Route $route): void
    {
        if (($index = $this->getIndex()) && $route->rule == "/") {
            unset($this->routes[$index->name]);
        }
        if (isset($this->routes[$route->name]))
            throw new \RuntimeException("A root is overriding an existing route name $route->name");
        $this->routes[$route->name] = $route;
    }

    /**
     * @param string $name
     * @return Route|null
     */
    public function get(string $name): ?Route
    {
        if (isset($this->routes[$name]))
            return $this->routes[$name];
        return null;
    }

    public function update(Route $route, string $oldName): void
    {
        if (isset($this->routes[$oldName])) {
            $this->add($route);
            unset($this->routes[$oldName]);
        }
    }

    public function getAll(): array
    {
        return $this->routes;
    }

    public function get404(): ?Route
    {
        foreach ($this->routes as $route) {
            if ($route->code_handler == 404) {
                return $route;
            }
        }
        return null;
    }

    public function getIndex(string $prefix = ""): ?Route
    {
        foreach ($this->routes as $route) {
            if ($route->rule == $prefix . "/") {
                return $route;
            }
        }
        return null;
    }

    public function filterBy()
    {

    }
}