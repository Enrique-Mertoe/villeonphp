<?php
/**
 * @author SmallVilleCycle
 * @author_email smallvillecycle5@gmail.com
 *  +--------------------------------------------+
 *  Copyright (c) 2024 SmallVille. All rights reserved.
 */

namespace Villeon\Theme;

use JetBrains\PhpStorm\NoReturn;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;
use Villeon\Core\Facade\Route;
use Villeon\Core\OS;

class ThemeBuilder
{
    private string $theme_dir;
    private string $self_theme;
    private Environment $env;
    public static ThemeBuilder $instance;

    public function __construct()
    {
        self::$instance = $this;

    }

    public function initialize($content_directory): void
    {
        if (is_dir($content_directory)) {
            $theme = $content_directory . "/public";
            if (is_dir($theme)) {
                $this->init_theme($theme);
            }
        }
        $this->self_theme = OS::ROOT . "/Theme";
        $this->env = new Environment(new FilesystemLoader($this->self_theme . "/layout/"));

        Route::get("/static/{filename:all}", function ($filename) {
            return $this->get($filename);
        });
    }

    private function init_theme($dir): void
    {
        $this->theme_dir = $dir;
    }


    /**
     * @param $file
     * @return false|int
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    private function get($file)
    {
        $file = $this->theme_dir . "/$file";
        if (!file_exists($file)) {
            header("HTTP/1.1 404 Not Found");
            $this->display_404();
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
     * @param array $info
     * @return void
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function display_error(array $info): void
    {
//        $content = readfile($file);
        echo $this->env->render("exception_handler.twig", $info);
    }

    /**
     * @return void
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    #[NoReturn] public function display_404(): void
    {
        try {
            echo $this->env->render("error_404.twig");
        } finally {
            exit();
        }
    }
}
