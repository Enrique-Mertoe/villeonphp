<?php

namespace Villeon\Theme;

use Twig\Loader\LoaderInterface;
use Twig\TwigFunction;
use Villeon\Utils\Console;

class Environment extends \Twig\Environment
{
    public function __construct(LoaderInterface $loader, array $options = [])
    {
        parent::__construct($loader, $options);
        $this->extend_methods();

    }

    private function extend_methods(): void
    {
        $this->addFunction(new TwigFunction("url_for", function ($endpoint, ...$args) {
            return url_for($endpoint,null, ...$args[0]);
        }));
    }
}