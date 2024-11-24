<?php

namespace Villeon\Core\Routing;

use Villeon\Http\Request;
use Villeon\Utils\Console;

class Route
{
    /**
     * @var \Closure $controller
     */
    public \Closure $controller;

    /**
     * @var string[] $allowed_methods
     */
    public array $allowed_methods;

    /**
     * @var string $rule
     */
    public string $rule;

    /**
     * @var int|null $code_handler
     */
    public ?int $code_handler = null;

    /**
     * @var string $name
     */
    public string $name;
    public ?string $prefix;

    /**
     * @var array<string,mixed> $config
     */
    public array $config;

    /**
     * @var array<string,string> $required_params
     */
    public array $required_params;

    /**
     * @param string $rule
     * @param string[] $methods
     * @param \Closure $controller
     * @param array ...$options
     */
    public function __construct(string $rule, array $methods, \Closure $controller, ...$options)
    {
        $this->rule = $rule;
        $this->controller = $controller;
        $this->allowed_methods = $methods;
        $this->config_options($options);
    }

    /**
     * Sets the name of given Route
     * @param string $name
     * @return Route
     */
    public function name(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param array $options
     * @return void
     */
    private function config_options(array $options)
    {

    }

    public function match($url): array
    {
        if ($this->prefix)
            $this->rule = $this->prefix . $this->rule;
        $this->normalise_rule();
        $pattern = '#^' . preg_replace('/<(\w+):path>/', '(.+)', preg_replace('/<(\w+)>/', '([^/]+)', $this->rule)) . '$#';
        $mapping = [];
        $regex = preg_replace_callback('#\{(!?)([\w\x80-\xFF]++)(:[\w\x80-\xFF]++)?(<.*?>)?(\?[^\}]*+)?\}#', function ($m) use (&$mapping) {
            $name = $m[2];
            $type = $m[3] ?? ':string';
            $regex = '([^/]+)';
            if ($type == ":path") {
                $regex = '(.+?\.[a-zA-Z0-9]+)';
            } elseif ($type === ':int') {
                $regex = '(\d+)';
            }

            $mapping[$name] = $regex;
            return $regex;
        }, $this->rule);
        if (preg_match('#^' . $regex . '$#', $url, $matches)) {
            array_shift($matches);
            $this->required_params = array_combine(array_keys($mapping), $matches);
            return $matches;
        }

//        if (preg_match($pattern, $url, $matches)) {
//            Console::Write("matched $this->rule and $url");
//
////            $this->checkAllowedMethods($route->method, Request::$method);
////            if (($ar = array_slice($matches, 1)) && $this->isDefined($ar)) {
////                continue;
////            }
////
////            $this->dispatch($route->rule, $route->controller, array_slice($matches, 1));
////
//            print_r($matches);
//            return [];
//        }
        return [];
    }

    function normalise_rule(): void
    {
        $this->rule = preg_replace('/\s+/', '', $this->rule);
    }
}