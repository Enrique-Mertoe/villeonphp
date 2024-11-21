<?php
/**
 * @author SmallVilleCycle
 * @author_email smallvillecycle5@gmail.com
 *  +--------------------------------------------+
 *  Copyright (c) 2024 SmallVille. All rights reserved.
 */

namespace Villeon\Core\Rendering;

use Villeon\core\Theme\ThemeBuilder;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Villeon\Http\Request;

class Render
{
    private static Environment $twig;

    private static function initTemplateEngine(): void
    {
        global $SRC;
        $loader = new FilesystemLoader("$SRC/layout/");
        self::$twig = new Environment($loader);
    }

    public static function Template($name, array $options = []): string
    {
        if (!isset(self::$twig)) {
            self::initTemplateEngine();
        }
        return (new Render())->builder($name, $options);
    }


    public static function Json(array $context = []): string
    {
        header('Content-Type: application/json');
        return json_encode($context);

    }

    private static function tData($cont, $type): array
    {
        return ["src" => $cont, "type" => $type];
    }

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

    private function builder($name, $options): string
    {
        return self::$twig->render($name, $this->process_options($options));
    }

}

function template($name, array $options = null
): string
{
    return Render::Template($name, $options ?? []);
}

function jsonify(array $context = []): string
{
    return Render::Json($context);
}
