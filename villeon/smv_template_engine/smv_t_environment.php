<?php

namespace SMVTemplating;

use SMVTemplating\Filter\SMVTemplateFilter;
use SMVTemplating\Loader\FilesystemLoader;

class Environment
{
    private static string $VAR_START_TAG = "{{";
    private static string $VAR_END_TAG = "}}";
    private static string $STRUCT_START_TAG = "{%";
    private static string $STRUCT_END_TAG = "%}";
    private FilesystemLoader $loader;
    private array $levelStack = [];

    public function __construct(FilesystemLoader $loader)
    {
        $this->loader = $loader;
    }

    public function render($name, $options): string
    {
        if ($file = $this->loader->get_template($name)) {
            return Interpolator::bind($file, $options);
        }
        return "Template $name is not found";
    }



}