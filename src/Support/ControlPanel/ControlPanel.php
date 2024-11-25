<?php

namespace Villeon\Support\ControlPanel;

use Villeon\Core\Facade\Extension;
use Villeon\Core\Routing\Blueprint;
use Villeon\Support\Extensions\ExtensionBuilder;

final class ControlPanel extends ExtensionBuilder
{

    public function __construct()
    {
        Extension::add("control_panel", builder: $this);
    }

    public static function builder(): ControlPanel
    {
        return new ControlPanel();
    }

    public function build(): void
    {
        $this->build_routes();

    }

    public function build_routes(): void
    {
        $bp = Blueprint::define("panel", url_prefix: "/control-panel");
        $bp->get("/", function () {
            return "control panel home";
        })->name("dashboard");
        $bp->get("/control", function () {
            return "control panel";
        });

    }
}