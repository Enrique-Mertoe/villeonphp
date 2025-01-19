<?php

namespace Villeon\Support\Admin;

use Exception;
use Twig\Loader\FilesystemLoader;
use Villeon\Core\Facade\Extension;
use Villeon\Core\Messages;
use Villeon\Core\OS;
use Villeon\Core\Routing\Blueprint;
use Villeon\Core\Session;
use Villeon\Error\RuntimeError;
use Villeon\Http\Request;
use Villeon\Support\Extensions\ExtensionBuilder;
use Villeon\Theme\Environment\Environment;

class AdminPanel extends ExtensionBuilder
{
    private Environment $environment;

    public function __construct()
    {
        $this->environment = new Environment(new FilesystemLoader(OS::ROOT . "/Theme/layout/admin"));

        Extension::add("admin_panel", builder: $this);
    }


    public static function builder(): static
    {
        return new static();
    }

    function build(): void
    {
        $this->build_routes();
    }

    private function build_routes(): void
    {
        $bp = Blueprint::define("admin", url_prefix: "/admin");
        $bp->get("/", function () {
            return "Admin home";
        })->name("dashboard");
        $bp->route("/auth/login", ["GET", "POST"], function () {
            if (Request::isPost()) {
                [$email, $password] = array_values(Request::$form->array());
                if (AdminAuth::login($email, $password)) {
                    Session::set("admin-session", $email);
                    return redirect(url_for("panel.dashboard"));
                } else
                    flash("Unable to login_admin",Messages::ERROR);
            }
            return $this->render("auth.twig");
        })->name("auth");
    }

    public function render($name, $args = []): string
    {
        try {
            return $this->environment->render($name, $args);
        } catch (Exception $e) {
            throw new RuntimeError($e->getMessage());
        }
    }
}
