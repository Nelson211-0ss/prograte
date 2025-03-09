<?php
// Enable error reporting for development
if ($_SERVER['SERVER_NAME'] === 'localhost' || strpos($_SERVER['SERVER_NAME'], '127.0.0.1') !== false) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Define the base path for the API
define('BASE_PATH', __DIR__);

// Basic routing
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

// Remove '/api' prefix if it exists
$path = str_replace('/api', '', $path);

// Route the request
switch ($path) {
    case '/forms':
        require __DIR__ . '/handle_forms.php';
        break;
    
    default:
        // Return 404 for undefined routes
        header('HTTP/1.1 404 Not Found');
        echo json_encode(['error' => 'Route not found']);
        break;
}
?>