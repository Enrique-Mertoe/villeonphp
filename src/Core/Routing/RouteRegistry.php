<?php

namespace Villeon\Core\Routing;


use RuntimeException;
use Villeon\Error\RuntimeError;
use Villeon\Utils\Console;

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

    private static function get_endpoint_details($endpoint): array
    {
        $segments = explode(".", $endpoint, 2);
        if (count($segments) > 1) {
            return [$segments[1], $segments[0]];
        }
        return [$segments[0], self::DEFAULT_BLUEPRINT];
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
            throw new RuntimeError("Cannot build URL of null endpoint");

        [$endpoint, $blueprint] = self::get_endpoint_details($endpoint);
        if (isset(self::$resolvedInstances[$blueprint])) {

            $bp = self::$resolvedInstances[$blueprint];
            if ($route = $bp->get_defined_routes()->get($endpoint)) {
                $params = $route->get_rule_params();
                $url_args = array_map(function ($param) use (&$args) {
                    if (!isset($args[$param])) {
                        throw new RuntimeError("Parameter $param is not set");
                    }

                    $value = $args[$param];
                    unset($args[$param]);
                    return $value;
                }, $params);
                return $route->build_endpoint($url_args, $args);
            }
        }
        $target = str_replace("default", "", $blueprint) . $endpoint;
        throw new RuntimeException("Cannot build url endpoint for $target. Ensure your Route has a name by assigning ->name(route-name)");

    }

}
