<?php

namespace Villeon\Core\Routing;

class RouteCollection
{
    /**
     * @var Route[]
     */
    private array $routes;

    public function add(Route $route): void
    {
        $this->routes[] = $route;
    }

    /**
     * @param string $name
     * @return Route|null
     */
    public function get(string $name): ?Route
    {
        foreach ($this->routes as $route) {
            if ($route->name == $name) {
                return $route;
            }
        }
        return null;
    }

    public function getAll(): array
    {
        return $this->routes;
    }
    public function get404(): ?Route
    {
        foreach ($this->routes as $route) {
            print_r($route->rule);
            if ($route->code_handler == 404) {
                return $route;
            }
        }
        return null;
    }

    public function filterBy()
    {

    }
}