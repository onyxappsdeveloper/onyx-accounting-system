<?php
/**
 * ONYX Accounting System - Core Application Class
 * Handles application initialization and bootstrapping
 */

namespace App\Core;

class App
{
    private static $instance;
    private $config = [];
    private $services = [];
    private $database;
    private $router;
    private $auth;
    private $session;
    private $tenant;

    /**
     * Get singleton instance
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Private constructor - Singleton
     */
    private function __construct()
    {
        $this->bootstrap();
    }

    /**
     * Bootstrap the application
     */
    private function bootstrap()
    {
        // Define application paths
        $this->definePaths();

        // Load configuration
        $this->loadConfig();

        // Initialize services
        $this->initializeServices();

        // Set up error handling
        $this->setupErrorHandling();
    }

    /**
     * Define application paths
     */
    private function definePaths()
    {
        if (!defined('BASE_PATH')) {
            define('BASE_PATH', dirname(dirname(dirname(__FILE__))) . '/');
            define('APP_PATH', BASE_PATH . 'app/');
            define('CONFIG_PATH', BASE_PATH . 'config/');
            define('ROUTES_PATH', BASE_PATH . 'routes/');
            define('STORAGE_PATH', BASE_PATH . 'storage/');
            define('UPLOADS_PATH', BASE_PATH . 'uploads/');
            define('VENDOR_PATH', BASE_PATH . 'vendor/');
            define('ASSETS_PATH', BASE_PATH . 'assets/');
        }
    }

    /**
     * Load configuration files
     */
    private function loadConfig()
    {
        // Load .env file
        $this->loadEnv();

        // Load configuration files
        $configFiles = ['app', 'database', 'mail', 'permissions'];
        foreach ($configFiles as $file) {
            $path = CONFIG_PATH . $file . '.php';
            if (file_exists($path)) {
                $this->config[$file] = require $path;
            }
        }
    }

    /**
     * Load environment variables
     */
    private function loadEnv()
    {
        $envFile = BASE_PATH . '.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    $_ENV[$key] = $value;
                    putenv("$key=$value");
                }
            }
        }
    }

    /**
     * Initialize services
     */
    private function initializeServices()
    {
        // Database connection
        $this->database = new Database($this->config['database'] ?? []);
        Database::setConnection($this->database);

        // Session
        $this->session = new Session();
        Session::setInstance($this->session);

        // Authentication
        $this->auth = new Auth();
        Auth::setInstance($this->auth);

        // Tenant
        $this->tenant = new Tenant();
        Tenant::setInstance($this->tenant);

        // Router
        $this->router = new Router();
    }

    /**
     * Set up error and exception handling
     */
    private function setupErrorHandling()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', $this->config['app']['debug'] ? '1' : '0');
        ini_set('log_errors', '1');
        ini_set('error_log', STORAGE_PATH . 'logs/error.log');

        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            $this->handleError($errno, $errstr, $errfile, $errline);
        });

        set_exception_handler(function ($exception) {
            $this->handleException($exception);
        });
    }

    /**
     * Handle PHP errors
     */
    public function handleError($errno, $errstr, $errfile, $errline)
    {
        error_log("[ERROR] $errstr in $errfile:$errline");
    }

    /**
     * Handle exceptions
     */
    public function handleException($exception)
    {
        error_log("[EXCEPTION] " . $exception->getMessage());
        
        if ($this->config['app']['debug']) {
            echo "<pre>" . $exception . "</pre>";
        } else {
            http_response_code(500);
            echo "Internal Server Error";
        }
    }

    /**
     * Run the application
     */
    public function run()
    {
        // Parse request
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $this->parseUri();

        // Load routes
        $this->loadRoutes();

        // Dispatch request
        try {
            $this->router->dispatch($method, $uri);
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Parse URI
     */
    private function parseUri()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $basePath = '/business/';
        
        if (strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }
        
        return trim($uri, '/') ?: 'dashboard';
    }

    /**
     * Load all routes
     */
    private function loadRoutes()
    {
        foreach (['auth', 'web', 'api'] as $routeFile) {
            $path = ROUTES_PATH . $routeFile . '.php';
            if (file_exists($path)) {
                $router = $this->router;
                require $path;
            }
        }
    }

    /**
     * Get configuration
     */
    public function config($key = null, $default = null)
    {
        if ($key === null) {
            return $this->config;
        }

        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $k) {
            if (isset($value[$k])) {
                $value = $value[$k];
            } else {
                return $default;
            }
        }

        return $value;
    }

    /**
     * Get database instance
     */
    public function database()
    {
        return $this->database;
    }

    /**
     * Get router instance
     */
    public function router()
    {
        return $this->router;
    }

    /**
     * Get auth instance
     */
    public function auth()
    {
        return $this->auth;
    }

    /**
     * Get session instance
     */
    public function session()
    {
        return $this->session;
    }

    /**
     * Get tenant instance
     */
    public function tenant()
    {
        return $this->tenant;
    }

    /**
     * Get router instance
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * Register a service
     */
    public function register($name, $service)
    {
        $this->services[$name] = $service;
    }

    /**
     * Get a registered service
     */
    public function service($name)
    {
        return $this->services[$name] ?? null;
    }
}
