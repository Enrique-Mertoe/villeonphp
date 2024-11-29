<?php


use Villeon\Core\Facade\Env;
use Villeon\Core\Facade\Render;
use Villeon\Core\Routing\Router;
use Villeon\Http\Response;

if (!function_exists('view')) {
    /**
     * @param string $template_name
     * @param array $context
     * @return string
     */
    function view(string $template_name, array $context = []): string
    {
        return Render::template($template_name, $context);
    }
}

if (!function_exists('jsonify')) {
    /**
     * @param array $context
     * @return Response
     */
    function jsonify(array $context = []): Response
    {
        return Render::json($context);
    }
}


if (!function_exists('response')) {
    /**
     * @param mixed $content
     * @return Response
     */
    function response(mixed $content): Response
    {
        return (new Response)->setContent($content);
    }
}

if (!function_exists('redirect')) {
    /**
     * @param string $location
     * @param int|null $code
     * @return Response
     */
    function redirect(string $location, ?int $code = null): Response
    {
        return (new Response)->setLocation($location)->setStatusCode($code);
    }
}

if (!function_exists('abort')) {
    /**
     * @param int|null $code
     * @return Response
     */
    function abort(int $code = null): Response
    {
        return new Response;
    }
}

if (!function_exists('url_for')) {
    /**
     * Build URL endpoint for a given Route
     * @param string $endpoint
     * @param bool|null $external
     * @param mixed ...$arguments
     * @return string     *
     */
    function url_for(string $endpoint, ?bool $external = null, ...$arguments): string
    {
        return Router::build_url_endpoint($endpoint, $external, $arguments);
    }
}

if (! function_exists('env')) {
    /**
     * Gets the value of an environment variable.
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    function env(string $key, mixed $default = null): mixed
    {
        return Env::get($key, $default);
    }
}
