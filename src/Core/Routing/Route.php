<?php

namespace Villeon\Core\Routing;

use Villeon\Http\Request;
use Villeon\Utils\Collection;
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

    public array $required_params = [];

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

    /**
     * Match a URL against the route's pattern to check if it corresponds to this route.
     * @param $url
     * @return array
     */
    public function match($url): array
    {
        if ($this->prefix)
            $this->rule = $this->prefix . $this->rule;
        $this->normalise_rule();
        $mapping = [];
        $mapper = function ($item, $regex) use (&$mapping) {
            $mapping[$item] = $regex;
        };
        $regex = preg_replace_callback('#\{(!?)([\w\x80-\xFF]++)(:[\w\x80-\xFF]++)?(<.*?>)?(\?[^\}]*+)?\}#', function ($m) use (&$mapper) {

            $name = $m[2];
            $type = $m[3] ?? ':string';
            $regex = match ($type) {
                ":all" => '(.*)',
                ":path" => '(.+?\.[a-zA-Z0-9]+)',
                ":int" => '(\d+)',
                default => '([^/]+)'
            };
            $mapper($name, $regex);
            return $regex;
        }, $this->rule);
        if (preg_match('#^' . $regex . '$#', $url, $matches)) {
            array_shift($matches);
            $this->required_params = array_combine(array_keys($mapping), $matches);
            return [true, $matches];
        }
        return [false, null];
    }

    function normalise_rule(): void
    {
        $this->rule = preg_replace('/\s+/', '', $this->rule);
    }
}