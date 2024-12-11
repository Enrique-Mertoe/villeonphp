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

// Get the current working directory (public path)
$cwd = getcwd();

// Decode the URL path from the request URI
// `parse_url` breaks down the URL, `PHP_URL_PATH` retrieves the path (e.g., `/index.php`)
// `urldecode` decodes any URL-encoded characters
$current_uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

// Check if the URI is not the root (/) and if the corresponding file exists
// If the file exists, the script will stop processing (return false)
// This allows the server to serve static files directly (like images, CSS, JS)
if ($current_uri !== '/' && file_exists($cwd . $current_uri)) {
    return false;
}
require_once $cwd . '/bootstrap/index.php';

