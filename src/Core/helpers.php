<?php

use Villeon\Core\Facade\Render;
use Villeon\Http\Response;

if (!function_exists('render_template')) {
    /**
     * @param string $name
     * @param array $context
     * @return string
     */
    function render_template(string $name, array $context = []): string
    {
        return Render::template($name, $context);
    }
}


if (!function_exists('response')) {
    /**
     * @param mixed $response
     * @return Response
     */
    function response(mixed $response): Response
    {
        $res = new Response;
        $res->setContent($response);
        return $res;
    }
}