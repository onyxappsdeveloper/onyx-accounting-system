<?php
/**
 * ONYX Accounting System
 * Entry Point for Application
 * 
 * @version 1.0.0
 * @author ONYX Tech Pay
 */

// Define paths
define('BASE_PATH', dirname(__DIR__) . '/../');
define('APP_PATH', BASE_PATH . 'app/');
define('CONFIG_PATH', BASE_PATH . 'config/');
define('ROUTES_PATH', BASE_PATH . 'routes/');
define('STORAGE_PATH', BASE_PATH . 'storage/');
define('UPLOADS_PATH', BASE_PATH . 'uploads/');
define('ASSETS_PATH', BASE_PATH . 'assets/');

// Load autoloader
require_once APP_PATH . 'autoload.php';

// Load helpers
require_once APP_PATH . 'Helpers/helpers.php';

// Load composer autoload
if (file_exists(BASE_PATH . 'vendor/autoload.php')) {
    require_once BASE_PATH . 'vendor/autoload.php';
}

// Initialize application
try {
    $app = \App\Core\App::getInstance();
    $GLOBALS['router'] = $app->router();
    $app->run();
} catch (Exception $e) {
    error_log('Application Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal Server Error']);
}
