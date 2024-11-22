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
use Villeon\Core\Exceptions\FileNotExistsException;
use Villeon\Core\OS;
use Villeon\Core\Routing\Route;

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
            $theme = $content_directory . "/public/static";
            if (is_dir($theme)) {
                $this->init_theme($theme);
            }
        }
        $this->self_theme = OS::ROOT . "/Theme";
        $this->env = new Environment(new FilesystemLoader($this->self_theme . "/layout/"));
        Route::get("/static/<filename :path>", function ($filename) {
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

        // Set the content type and serve the file
        header("Content-Type: " . $mime_type);
        header("Content-Length: " . filesize($file));
        $content = readfile($file);
        return $content;
    }

    public function prepare($file): string
    {
        return "/theme/$file";
    }

    private function getMimeType($file): string
    {
        $extension = pathinfo($file, PATHINFO_EXTENSION);

        switch ($extension) {
            case 'css':
                return 'text/css';
            case 'js':
                return 'application/javascript';
            case 'png':
                return 'image/png';
            case 'jpg':
            case 'jpeg':
                return 'image/jpeg';
            case 'gif':
                return 'image/gif';
            case 'svg':
                return 'image/svg+xml';
            case 'woff':
                return 'font/woff';
            case 'woff2':
                return 'font/woff2';
            case 'ttf':
                return 'font/ttf';
            case 'otf':
                return 'font/otf';
            case 'ico':
                return 'image/x-icon';
            default:
                return 'application/octet-stream'; // For other file types
        }
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


            http_response_code(404);
            echo $this->env->render("error_404.twig");
        }finally {
            exit();
        }
    }
}
