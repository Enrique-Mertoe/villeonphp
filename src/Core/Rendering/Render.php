<?php
/**
 * @author SmallVilleCycle
 * @author_email smallvillecycle5@gmail.com
 *  +--------------------------------------------+
 *  Copyright (c) 2024 SmallVille. All rights reserved.
 */

namespace Villeon\Core\Rendering;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;
use Villeon\Http\Request;
use Villeon\Theme\ThemeBuilder;

class Render
{
    private static Environment $twig;

    /**
     * @return void
     */
    private static function initTemplateEngine(): void
    {
        $loader = new FilesystemLoader(SRC . "/layout/");
        self::$twig = new Environment($loader);
    }

    /**
     * @param $name
     * @param array $options
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public static function Template($name, array $options = []): string
    {
        if (!isset(self::$twig)) {
            self::initTemplateEngine();
        }
        return (new Render())->builder($name, $options);
    }

    /**
     * Render a json based response
     * @param array $context
     * @return string
     */
    public static function Json(array $context = []): string
    {
        header('Content-Type: application/json');
        return json_encode($context);

    }

    private static function tData($cont, $type): array
    {
        return ["src" => $cont, "type" => $type];
    }

    /**
     * @param array $options
     * @return array
     */
    private function process_options(array $options): array
    {
        $theme = ThemeBuilder::$instance;
        return $options + [
                "session" => $_SESSION,
                "args" => Request::$args->array(),
                "form" => Request::$form->array(),
                "theme" => [
                    "css" => [
                        "default" => $theme->prepare("css/default.css")
                    ],
                    "js" => [],
                    "img" => []
                ]
            ];
    }

    /**
     * @param $name
     * @param $options
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    private function builder($name, $options): string
    {
        return self::$twig->render($name, $this->process_options($options));
    }

}

/**
 * @param $name
 * @param array|null $options
 * @return string
 * @throws LoaderError
 * @throws RuntimeError
 * @throws SyntaxError
 */
function template($name, array $options = null
): string
{
    return Render::Template($name, $options ?? []);
}

/**
 * @param array $context
 * @return string
 */
function jsonify(array $context = []): string
{
    return Render::Json($context);
}
