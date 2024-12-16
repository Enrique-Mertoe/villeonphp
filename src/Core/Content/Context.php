<?php

namespace Villeon\Core\Content;

use Villeon\Application;
use Villeon\Http\Request;
use Villeon\Http\Response;
use Villeon\Library\Collection\IMutableDict;
use Villeon\Theme\ThemeBuilder;

abstract class Context implements RequireContext
{
    public string $basePath;
    public string $staticDir;
    public string $templateDir;
    private IMutableDict $default_config;
    public Request $request;
    public Response $response;

    protected ThemeBuilder $theme;

    public Application $application;

    public function __construct($basePath)
    {
        $this->basePath = $basePath;
        $this->default_config = dictOf(["DEBUG" => null,
            "SECRET_KEY" => null,
            "SERVER_NAME" => null,
            "APPLICATION_ROOT" => "/",
            "SESSION_COOKIE_NAME" => "session",
            "SESSION_COOKIE_HTTPONLY" => True,
            "SESSION_COOKIE_SECURE" => False,
        ]);
    }

    public function getSrc(): string
    {
        return $this->basePath . "/src";
    }
}
