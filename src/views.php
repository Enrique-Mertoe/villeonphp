<?php

use Villeon\Core\Routing\Route;
use Villeon\Core\Session;
use function Villeon\Core\Rendering\jsonify;
use function Villeon\Core\Rendering\template;

Route::get("/", function () {
    echo "123";
    Session::set("name", "milla");
    return template("home.twig");
});
Route::get("/src", function () {
    echo "123";
    Session::set("name", "milla");
    return template("home.twig");
});


Route::route("/about", ["GET"], function () {
    echo "iii";
    return "Hello from about";
});

Route::route("/404", ["GET"], function () {
    return "djmd";
});

Route::error(404, function () {
    return "Hello from 404";
});
Route::route("/<name>/<name1>", function ($name, $name1) {
    return "Hello from $name $name1";
});
Route::route("/api", function () {
    return jsonify([
        "name" => "martin"
    ]);
});

