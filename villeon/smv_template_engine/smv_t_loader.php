<?php

namespace SMVTemplating\Loader;
class FilesystemLoader
{
    private string $root;

    public function __construct($template_directory)
    {
        $this->root = $template_directory;
    }

    public function get_template($name): string|null
    {
        $file = $this->root . "/$name";
        if (file_exists($file))
            return file_get_contents($file);
        return null;
    }


}