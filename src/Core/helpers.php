<?php


use Villeon\Application;
use Villeon\Core\Content\ContentManager;
use Villeon\Core\Content\Context;
use Villeon\Core\Facade\Env;
use Villeon\Core\Facade\Render;
use Villeon\Core\Messages;
use Villeon\Core\Routing\Router;
use Villeon\Http\Response;
use Villeon\Library\Collection\ImmutableList;
use Villeon\Library\Collection\Dict;
use Villeon\Library\Collection\IMutableDict;
use Villeon\Library\Collection\MList;
use Villeon\Library\Str;
use Villeon\Utils\Log;

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

if (!function_exists('env')) {
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

if (!function_exists("flash")) {
    /**
     * Flashes a message to the next request.  In order to remove the
     * flashed message from the session and to display it to the user,
     * the template has to call :func:`get_flashed_messages`.
     * @param string $message
     * @param int $category
     * @return void
     */
    function flash(string $message, int $category = Messages::MESSAGE): void
    {
        Messages::add($message, $category);
    }
}
if (!function_exists("get_flashed_messages")) {
    /**
     * Pulls all flashed messages from the session and returns them.
     * Further calls in the same request to the function will return
     * the same messages.
     * By default, just the messages are returned,
     * but when `with_categories` is set to ``true``, the return value will
     * be an ``array<key,value>`` instead.
     *
     * Filter the flashed messages to one or more categories by providing those
     * categories in `category_filter`.
     * @param bool $with_categories
     * @param array $category_filter
     * @return array
     */
    function get_flashed_messages(bool $with_categories = false, array $category_filter = []): array
    {
        return Messages::all($with_categories, $category_filter);
    }
}

if (!function_exists("listOf")) {
    /**
     * @param mixed ...$elements
     * @return ImmutableList
     */
    function listOf(...$elements): ImmutableList
    {
        return ImmutableList::from($elements);
    }
}
if (!function_exists("arrayOf")) {
    /**
     * @param mixed ...$elements
     * @return MList
     */
    function arrayOf(...$elements): MList
    {
        return MList::from($elements);
    }
}

if (!function_exists("mutableListOf")) {
    /**
     * @param ...$elements
     * @return MList
     */
    function mutableListOf(...$elements): MList
    {
        return MList::from($elements);
    }
}
if (!function_exists("str")) {
    /**
     * @param string $str
     * @return Str
     */
    function str(string $str): Str
    {
        return Str::from($str);
    }
}
if (!function_exists("dict")) {

    /**
     * Key value pair
     * @param array<string|int,mixed> $elements
     * @return Dict
     */
    function dict(array $elements = []): Dict
    {
        return Dict::from($elements);
    }
}

if (!function_exists("dictOf")) {

    /**
     * Key value pair
     * @param array<string|int,mixed> $elements
     * @return IMutableDict
     */
    function dictOf(array $elements): IMutableDict
    {
        return IMutableDict::from($elements);
    }
}


if (!function_exists("log_error")) {
    /**
     * @param Throwable|null $throwable
     * @return void
     */
    function log_error(?Throwable $throwable = null): void
    {
        Log::ErrorLog($throwable);
    }
}

if (!function_exists("app_context")) {
    function app_context(): Context
    {
        return Application::getContext();
    }
}
