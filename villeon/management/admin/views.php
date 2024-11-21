<?php

use Villeon\core\Route;

Route::route("/smv-admin", function () {
    return "hello admin";
});

Route::route("/smv-admin/<page_mame>", function ($page_name) {
    return "hello admin_page";
});
