<?php

namespace Villeon\Support\Admin;

use Villeon\Core\Facade\Extension;
use Villeon\Core\Routing\Blueprint;
use Villeon\Core\Session;
use Villeon\Support\Extensions\ExtensionBuilder;

class AdminPanel extends ExtensionBuilder
{
    public function __construct()
    {
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
        $bp->get("/auth/login", function () {
            Session::set('admin-session', true);
            return "Admin login";
        })->name("auth");
    }
}