<?php

namespace Villeon\Http;


use Villeon\Utils\Collection;

class Request
{
    /**
     * The HTTP method used for the request (GET, POST, PUT,etc.).
     * @var string $method
     */
    public static string $method;

    /**
     * The request URI
     * @var string $uri
     */
    public static string $uri;


    /**
     * The request headers
     * @var array<string,string> $headers
     */
    public static array $headers;

    /**
     * The request body content
     * @var mixed $body
     */
    public static mixed $body;


    /**
     * The query string parameters from the URL.
     * @var array<string, string> $args
     */
    public static array $args;


    /**
     * The form data from POST requests.
     * @var Collection $form
     */
    public static Collection $form;


    /**
     * The json data from requests.
     * @var Collection $json
     */
    public static Collection $json;

    public function __construct()
    {
    }

    public function __destruct()
    {
    }

    public static function form($param = null): array|null
    {
        if ($param)
            return $_POST[$param] ?? null;
        return $_POST;
    }

    public static function args($param = null): array|string|null
    {
        if ($param)
            return $_GET[$param] ?? null;
        return $_GET;
    }

    public static function method($method = null): bool|string
    {
        if ($method)
            return $_SERVER["REQUEST_METHOD"] == $method;
        return $_SERVER["REQUEST_METHOD"];
    }

    public function get($name)
    {
    }

    public function post($name)
    {
    }

    public function delete($name)
    {
    }

    public function update($name)
    {
    }

    function build(): void
    {
        self::$form = Collection::from_array($_POST);
        self::$args = $_GET;
        self::$method = $_SERVER["REQUEST_METHOD"];
        $uri = parse_url(urldecode($_SERVER['REQUEST_URI']));
        self::$uri = trim(preg_replace('#/+#', '/', $uri["path"]));
        self::$headers = getallheaders();

        $input = file_get_contents('php://input');
        self::$body = $input;
        if ($this->isJson()) {
            self::$json = Collection::from_array(json_decode($input, true));
        }


    }

    private static function isJson(): bool
    {
        $contentType = self::getHeader('Content-Type') ?? "";
        return str_contains($contentType, 'application/json');
    }

    /**
     * @param string $header
     * @return string|null
     */
    public static function getHeader(string $header): ?string
    {
        return self::$headers[$header] ?? null;
    }

    /**
     * @return bool
     */
    public static function isPost(): bool
    {
        return self::$method === 'POST';
    }

    /**
     * @return bool
     */
    public static function isGet(): bool
    {
        return self::$method === 'GET';
    }
}
