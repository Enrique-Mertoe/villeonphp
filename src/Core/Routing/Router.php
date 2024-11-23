<?php

namespace Villeon\Core\Routing;

use Villeon\Utils\Collection;

class Router
{
    private static array $route_config = [];
    private static array $route_error_config = [];

    public function __construct()
    {
    }

    /**
     * @return object
     */
    function build(): object
    {
        return (object)[
            "errors" => self::$route_error_config,
            "rules" => self::$route_config,
        ];
    }

    /**
     * @param $rule
     * @param ...$options
     * @return void
     */
    public function route($rule, ...$options): void
    {
        self::$route_config[] = _UrlConfig::from_array(
            array_merge([
                "rule" => $rule,
            ], self::process_options($options))
        );
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
     * @param callable $controller
     * @return void
     */
    public static function error(int $code, callable $controller): void
    {
        self::$route_error_config[] = _UrlConfig::from_array(
            array_merge([
                "rule" => $code,
            ], self::process_options([$controller]))
        );
    }

    public function post($rule, $controller): void
    {
        self::$route_config[] = _UrlConfig::from_array(
            array_merge([
                "rule" => $rule,
            ], [
                "methods" => ["POST"],
                "controller" => $controller
            ])
        );
    }

    public function get($rule, $controller): void
    {
        self::$route_config[] = _UrlConfig::from_array(
            array_merge([
                "rule" => $rule,
            ], [
                "methods" => ["GET"],
                "controller" => $controller
            ])
        );
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
