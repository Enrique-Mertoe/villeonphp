<?php

use Villeon\Core\Facade\Render;
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
