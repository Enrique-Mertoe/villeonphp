<?php
/**
 * @author SmallVilleCycle
 * @author_email smallvillecycle5@gmail.com
 *  +--------------------------------------------+
 *  Copyright (c) 2024 SmallVille. All rights reserved.
 */

namespace Villeon\Core\Rendering;

use Villeon\Core\Content\AppContext;
use Villeon\Core\Facade\Facade;
use Villeon\Http\Request;
use Villeon\Http\Response;
use Villeon\Theme\Environment\Environment;
use Villeon\Theme\ThemeBuilder;

class RenderBuilder
{
    private Environment $twig;
    private AppContext $context;

    public function __construct(AppContext $context)
    {
        $this->context = $context;
        $this->twig = $this->context->getEnv();
    }

    public static function config(AppContext $context): void
    {
        Facade::setFacade("render", new RenderBuilder($context));
    }

    /**
     * @param $name
     * @param array $context
     * @return string
     */
    public function template($name, array $context = []): string
    {
        return $this->twig->render($name, $this->process_options($context));
    }

    /**
     * Render a json based response
     * @param array $context
     * @return Response
     */
    public function Json(array $context = []): Response
    {
        return (new Response(json_encode($context)))
            ->setHeader("Content-Type", "application/json");
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
                "args" => Request::$args,
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

}
