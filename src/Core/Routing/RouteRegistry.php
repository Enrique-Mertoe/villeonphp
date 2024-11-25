<?php

namespace Villeon\Core\Routing;


use RuntimeException;
use Villeon\Error\RuntimeError;

abstract class RouteRegistry
{
    /**
     *
     */
    public const DEFAULT_BLUEPRINT = "default";
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

    public function get_defined_routes(): RouteCollection
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

    /**
     * @param string $blue_print
     * @return RouteRegistry|null
     */
    public static final function get_blueprint(string $blue_print = self::DEFAULT_BLUEPRINT): ?RouteRegistry
    {
        if (isset(self::$resolvedInstances[$blue_print]))
            return self::$resolvedInstances[$blue_print];
        return null;
    }

    /**
     * @param $prefix
     * @return RouteRegistry|null
     */
    public static final function get_by_prefix($prefix): ?RouteRegistry
    {
        foreach (self::$resolvedInstances as $instance) {
            if ($instance->prefix == $prefix)
                return $instance;
        }
        return null;
    }

    /**
     * @param string $endpoint
     * @param bool|null $external
     * @param array $args
     * @return string
     */
    public static final function build_url_endpoint(string $endpoint, ?bool $external, array $args): string
    {
        if (empty($endpoint))
            throw new RuntimeError("Cannot build url of null endpoint");
        $segments = explode(".", $endpoint, 2);
        if (count($segments) > 1) {
            $blueprint = $segments[0];
            $endpoint = $segments[1];
        } else {
            $blueprint = self::DEFAULT_BLUEPRINT;
            $endpoint = $segments[0];
        }

        if (!empty($blueprint)) {
            if (!isset(self::$resolvedInstances[$blueprint])) {
                throw new RuntimeError("");
            }
            $bp = self::$resolvedInstances[$blueprint];
            if ($route = $bp->get_defined_routes()->get($endpoint)) {
                $params = $route->get_rule_params();
                if (!empty($params)) {
                    {
                        $url_args = [];
                        foreach ($params as $param) {
                            if (isset($args[$param])) {
                                $url_args[] = $args[$param];
                                unset($args[$param]);
                            } else
                                throw new RuntimeError("Parameter $param is not set");
                        }
                        return $route->build_endpoint($url_args, $args);
                    }
                }
                return $route->rule;
            }
        }

        foreach (self::$resolvedInstances as $instance) {
            foreach ($instance->get_defined_routes()->getAll() as $item) {
                if ($item->rule == $endpoint) {
                    return $item->rule;
                }
            }
        }
        throw new RuntimeError("Cannot build url endpoint for $endpoint. Ensure your Route has a name by assigning ->name(route-name)");

    }

}