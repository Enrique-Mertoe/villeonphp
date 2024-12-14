<?php

namespace Villeon\Core\Kernel;

use JetBrains\PhpStorm\NoReturn;
use Villeon\Core\Routing\Route;

class Dispatcher
{
    private Route $route;
    public function __construct(Route $route)
    {
        $this->route = $route;
    }

    #[NoReturn] public function dispatch(): void
    {

        exit;
    }
    public static function from(Route $route): Dispatcher
    {
        return new Dispatcher($route);
    }
    #[NoReturn] public static function redirect(string $location): void
    {
        header("Location: $location");
        exit();
    }

}
