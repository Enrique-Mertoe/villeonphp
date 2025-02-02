<?php

namespace Villeon\Support\ControlPanel;

abstract class PanelHandler
{
    abstract public static function action(string $name, array $args);
}
