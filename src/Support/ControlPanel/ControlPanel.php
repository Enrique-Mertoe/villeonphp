<?php

namespace Villeon\Support\ControlPanel;

use Twig\Loader\FilesystemLoader;
use Villeon\Core\Facade\Extension;
use Villeon\Core\OS;
use Villeon\Core\Routing\Blueprint;
use Villeon\Error\RuntimeError;
use Villeon\Http\Request;
use Villeon\Support\Extensions\ExtensionBuilder;
use Villeon\Theme\Environment;

final class ControlPanel extends ExtensionBuilder
{
    private Environment $environment;

    public function __construct()
    {
        $this->environment = new Environment(new FilesystemLoader(OS::ROOT . "/Theme/layout/panel"));
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
            return $this->render("base.twig");
        })->name("dashboard");
        $bp->post("/actions", function () {
            $r = [$this, "render"];
            return ActionBuilder::get(Request::args("type"), $r);
        });

    }

    public function render($name, ...$args): string
    {
        try {
            return $this->environment->render($name, $args);
        } catch (\Exception $e) {
            throw new RuntimeError($e->getMessage());
        }
    }
}