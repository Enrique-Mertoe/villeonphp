<?php

namespace Villeon\Http;

use Villeon\core\Collection\Collection;

class Request
{
    public static $method;


    public static Collection $args;


    public static Collection $form;


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

    public static function args($param = null): array|null
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
        self::$args = Collection::from_array($_GET);
        self::$method = $_SERVER["REQUEST_METHOD"];


        $input = file_get_contents('php://input');
        Request::$json = Collection::from_array(json_decode($input, true));

    }
}
