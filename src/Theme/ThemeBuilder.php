<?php
/**
 * @author SmallVilleCycle
 * @author_email smallvillecycle5@gmail.com
 *  +--------------------------------------------+
 *  Copyright (c) 2024 SmallVille. All rights reserved.
 */

namespace Villeon\Theme;

use JetBrains\PhpStorm\NoReturn;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;
use Villeon\Core\Content\AppContext;
use Villeon\Core\Content\Context;
use Villeon\Core\Content\AppCombat;
use Villeon\Core\Facade\Route;
use Villeon\Core\OS;
use Villeon\Theme\Environment\Environment;
use Villeon\Theme\Environment\ThemeEnvironment;
use Villeon\Utils\Console;

class ThemeBuilder
{
    private string $static_dir;
    private string $template_dir;
    private string $self_theme;
    private Environment $env;
    public static ThemeBuilder $instance;
    private Context $context;
    private Environment $appEnv;

    public function __construct(Context $context)
    {
        self::$instance = $this;
        $this->context = $context;
        $this->initialize();

    }

    private function initialize(): void
    {
        if (is_dir($this->context->basePath)) {
            $this->static_dir = $this->context->basePath . "/public";
            $this->template_dir = $this->context->basePath . "/templates";
        }
        $this->context->setStaticDir($this->static_dir);
        $this->context->setTemplateDir($this->template_dir);
        $this->self_theme = OS::ROOT . "/Theme";
        $this->env = new ThemeEnvironment(new FilesystemLoader($this->self_theme . "/layout/"));
        $this->appEnv = new Environment(new FilesystemLoader($this->template_dir));
        $this->init_static();
    }

    public function getRenderEnv(): Environment
    {
        return $this->appEnv;
    }


    /**
     * @param $file
     * @return false|string
     */
    private function get($file)
    {
        $file = str($file)->trim();
        if ($file->startsWith("villeon/"))
            $file = $this->self_theme . "/assets/" . $file->replace("villeon/", "");
        else
            $file = $this->static_dir . "/$file";
        if (!file_exists($file)) {
            header("HTTP/1.1 404 Not Found");
            $this->display_error_page(404);
        }
        $mime_type = $this->getMimeType($file);

        header("Content-Type: " . $mime_type);
        header("Content-Length: " . filesize($file));
        ob_start();
        readfile($file);
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    public function prepare($file): string
    {
        return "/theme/$file";
    }

    private function init_static(): void
    {
        Route::get("/static/{filename:all}", function ($filename) {
            return $this->get($filename);
        })->name("static");
        Route::get("/", function () {
            return $this->env->render("home.twig");
        })->name("default_home");
    }

    private function getMimeType($file): string
    {

        $extension = pathinfo($file, PATHINFO_EXTENSION);
        return match ($extension) {
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'otf' => 'font/otf',
            'ico' => 'image/x-icon',
            default => 'application/octet-stream',
        };
    }

    /**
     * @param \Throwable $exception
     * @return string
     */
    public function display_error(\Throwable $exception): string
    {
        $info = [
            "error" => [
                "message" => $exception->getMessage(),
                "line" => $exception->getLine(),
                "trace" => $exception->getTrace(),
                "file" => $exception->getFile(),
                "code" => $exception->getCode(),
                "class" => $exception->getTrace()[0],

            ]
        ];
        return $this->env->render("exception_handler.twig", $info);
    }

    /**
     * @param int $code
     * @return string
     */
    public function display_error_page(int $code): string
    {
        return $this->env->render("error_page.twig", ["error" => $code]);
    }
}
