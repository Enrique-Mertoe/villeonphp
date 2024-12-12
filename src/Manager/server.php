<?php
/**
 * server.php
 * @package    Villeon\Manager
 * @author     Abuti Martin <abutimartin778@gmail.com>
 * @copyright  2024 Villeon
 * @license    MIT License
 * @version    1.1.0
 * @link       https://github.com/Enrique-Mertoe/villeonphp
 */

$cwd = getcwd();
$current_uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);
if ($current_uri !== '/' && file_exists($cwd ."/public/" . $current_uri)) {
    return readfile($cwd ."/public/" . $current_uri);
}
require_once $cwd . '/www/index.php';
