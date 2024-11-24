<?php

namespace Villeon\Core\Routing;

abstract class RouteRegistry
{
    /**
     * @var RouteRegistry[]
     */
    private static array $resolvedInstances;
    protected RouteCollection $routes;
    protected RouteCollection $error_handlers;
    protected string $name;
    protected ?string $prefix = null;

    public function __construct($name)
    {
        $this->name = $name;
        self::$resolvedInstances[$name] = $this;
        $this->routes = new RouteCollection;
        $this->error_handlers = new RouteCollection;
    }

    public function getBluePrints(): RouteCollection
    {
        return $this->routes;
    }
    public function getErrorHandlerBluePrint(): RouteCollection
    {
        return $this->error_handlers;
    }

    protected static array $route_error_config = [];
    protected static array $route_config = [];

    /**
     * @return RouteRegistry[]
     */
    public static final function build(): array
    {
        return self::$resolvedInstances;
    }

}