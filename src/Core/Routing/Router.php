<?php

namespace Villeon\Core\Routing;

use Villeon\Utils\Collection;

class Router extends RouteRegistry
{


    /**
     * @param $rule
     * @param ...$options
     * @return void
     */
    public function route($rule, ...$options): void
    {
        $this->addRoute($rule, ...self::process_options($options));
    }

    /**
     * @param $options
     * @return array
     */
    private static function process_options($options): array
    {
        $options = Collection::from_array($options);

        if ($options->size == 1 && is_callable($options[0]))
            return [
                "methods" => ["GET"],
                "controller" => $options[0]
            ];
        elseif ($options->size == 1 && !is_callable($options[0]))
            return [
                "methods" => ["GET"],
                "controller" => null
            ];
        if ($options->size > 1) {

            $option1 = $options[0];
            $option2 = $options[1];
            if (is_array($option1) && is_callable($option2)) {
                return [
                    "methods" => $option1,
                    "controller" => $option2
                ];
            }
        }
        return [];
    }

    /**
     * @param int $code
     * @param \Closure $controller
     * @return Route
     */
    public function error_handler(int $code, \Closure $controller): Route
    {
        return $this->addErrorHandler($code, $controller);
    }

    public function post($rule, \Closure $controller): Route
    {
        return $this->addRoute($rule, ["POST"], $controller);
    }

    public function get(string $rule, \Closure $controller): Route
    {
        return $this->addRoute($rule, ["GET"], $controller);
    }

    /**
     * @param string $rule
     * @param array $methods
     * @param \Closure $controller
     * @return Route
     */
    private function addRoute(string $rule, array $methods, \Closure $controller): Route
    {
        $route = new Route($rule, $methods, $controller);
        $route->prefix = $this->prefix;
        $this->routes->add($route);
        return $route;
    }

    private function addErrorHandler(int $code, \Closure $controller): Route
    {
        $methods = ["GET"];
        $route = new Route((string)$code, $methods, $controller);
        $route->prefix = $this->prefix;
        $route->code_handler = $code;
        $this->error_handlers->add($route);
        return $route;
    }

}

class _UrlConfig
{
    public mixed $controller;
    public mixed $method;
    public mixed $rule;

    public function __construct($properties)
    {
        $this->rule = $properties["rule"] ?? "";
        $this->method = $properties["methods"] ?? "";
        $this->controller = $properties["controller"] ?? null;
    }

    public static function from_array($arr): _UrlConfig
    {
        return new _UrlConfig($arr);
    }

}
