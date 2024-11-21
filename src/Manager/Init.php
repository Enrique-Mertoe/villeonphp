<?php
require("CommandLine.php");
if (!defined('BASE_DIR')) {
    define('BASE_DIR', __DIR__ . '/');
}
$BASE_DIR = BASE_DIR;
if (file_exists(BASE_DIR . 'smv-config.php'))
    require_once BASE_DIR . "smv-config.php";
