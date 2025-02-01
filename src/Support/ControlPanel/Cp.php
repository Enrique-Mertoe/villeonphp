<?php

namespace Villeon\Support\ControlPanel;

use Villeon\Manager\Manager;

class Cp
{
    public static function getAllModels(): array
    {
        $models = Manager::getModels();
        print_r($models);
        return $models;
    }
}
