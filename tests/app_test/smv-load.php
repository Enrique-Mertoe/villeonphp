<?php

use Villeon\Application;
use Villeon\Config\Config;

require "../../vendor/autoload.php";
require "smv-config.php";

$app = new Application();
$app->withConfig(function () {
    Config::set_src(SRC);
})->create();