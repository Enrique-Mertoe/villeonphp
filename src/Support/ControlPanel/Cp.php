<?php

namespace Villeon\Support\ControlPanel;

use Villeon\Manager\Handlers\ModelRegistry;

class Cp
{
    public static function getAllModels(): array
    {
        $models = ModelRegistry::getModels();
        print_r($models);
        return $models;
    }
}
