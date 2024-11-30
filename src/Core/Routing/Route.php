<?php

namespace Villeon\Core\Routing;

use Villeon\Error\RuntimeError;
use Villeon\Http\Request;
use Villeon\Utils\Console;
use Villeon\Utils\Log;

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
     * @var string | null $name
     */
    public ?string $name = null;
    public ?string $prefix;

    /**
     * @var array<string,mixed> $config
     */
    public array $config;

    public RouteRegistry $registry;

    /**
     * @param string $rule
     * @param string[] $methods
     * @param \Closure $controller
     * @param RouteRegistry $registry
     * @param array ...$options
     */
    public function __construct(string $rule, array $methods, \Closure $controller, RouteRegistry $registry, ...$options)
    {
        $this->rule = $rule;
        $this->controller = $controller;
        $this->allowed_methods = $methods;
        $this->registry = $registry;
        $this->config_options($options);
        $this->name = $this->define_name();
    }

    /**
     * Sets the name of given Route
     * @param string $name
     * @return Route
     */
    public function name(string $name): static
    {
        try {
            $old = $this->name;
            $this->name = $name;
            $this->registry->get_defined_routes()->update($this, $old);
        } catch (\Exception $e) {
            throw new RuntimeError($e->getMessage());
        }
        return $this;
    }

    private function define_name(): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < 10; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }

        return "default" . $randomString;
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
        $rule = $this->rule;
        if ($this->prefix)
            $rule = $this->prefix . $this->rule;
        $this->normalise_rule();
        $mapping = [];
        $mapper = function ($item, $regex) use (&$mapping) {
            $mapping[$item] = $regex;
        };
        $regex = preg_replace_callback('#\{(!?)([\w\x80-\xFF]++)(:[\w\x80-\xFF]++)?(<.*?>)?(\?[^}]*+)?}#', function ($m) use (&$mapper) {

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
        }, $rule);
        if (preg_match('#^' . $regex . '$#', $url, $matches)) {
            array_shift($matches);
            $this->required_params = array_combine(array_keys($mapping), $matches);
            return [true, $matches];
        }
        return [false, null];
    }

    /**
     * @return string[]
     */
    function get_rule_params(): array
    {
        $segments = explode('/', trim($this->rule, '/'));
        $params = [];
        foreach ($segments as $index => $segment) {
            if (preg_match('/\{(\w+)}/', $segment, $matches)) {
                $params[] = $matches[1];
            } elseif (preg_match('/\{(\w+:\w+)}/', $segment, $matches)) {
                $params[] = explode(":", $matches[1])[0];
            }
        }
        return $params;

    }

    function normalise_rule(): void
    {
        $this->rule = preg_replace('/\s+/', '', $this->rule);
    }

    /**
     * @param array $url_args
     * @param array $args
     * @return string
     */
    public function build_endpoint(array $url_args, array $args): string
    {
        $segments = explode('/', trim($this->rule, '/'));

        $url_args = array_reverse($url_args);
        $params = array_map(function ($segment) use (&$url_args) {
            return preg_match('/\{(\w+)(?::\w+)?}/', $segment) ? array_pop($url_args) : $segment;
        }, $segments);

        $queryString = !empty($args) ? '?' . http_build_query($args) : '';
        $path = implode('/', $params);
        $prefix = rtrim($this->prefix ?? '/', '/');
        $path = ltrim($path, '/');
        return $prefix . '/' . $path . $queryString;
    }

    public function method_allowed(): bool
    {
        return in_array(Request::$method, $this->allowed_methods);
    }
}