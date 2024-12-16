<?php

namespace Villeon\Support\ControlPanel;

use Twig\Loader\FilesystemLoader;
use Villeon\Core\Facade\Config;
use Villeon\Core\Facade\Extension;
use Villeon\Core\Facade\Settings;
use Villeon\Core\OS;
use Villeon\Core\Routing\Blueprint;
use Villeon\Core\Session;
use Villeon\Database\VilleonSQL\Model;
use Villeon\Error\RuntimeError;
use Villeon\Http\Request;
use Villeon\Support\Extensions\ExtensionBuilder;
use Villeon\Theme\Environment\Environment;

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
            if (Settings::get("PANEL_SECURED") && !Session::has("admin-session"))
                return redirect(url_for('admin.auth'));

            $options = [
//                "db" => Config::db_info(),
//                "tables" => Model::getAll()
            ];
            return $this->render("dashboard.twig", ['panel' => $options]);
        })->name("dashboard");
        $bp->post("/actions", function () {
            $r = [$this, "render"];
            return ActionBuilder::get(Request::args("type"), $r);
        });
        $bp->get("/settings", function () {
            return $this->render("settings.twig");
        })->name("settings");

    }

    public function render($name, $args = []): string
    {
        try {
            return $this->environment->render($name, $args);
        } catch (\Exception $e) {
            throw new RuntimeError($e->getMessage());
        }
    }
}
