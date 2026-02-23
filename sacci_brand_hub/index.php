<?php

// Front controller
use Core\Router;
use Config;

// Start session
session_start();

// Autoloader for PSR-4 style classes
spl_autoload_register(function ($class) {
    $prefixes = [
        'App\\' => __DIR__ . '/app/',
        'Core\\' => __DIR__ . '/core/',
        'Config\\' => __DIR__ . '/config/',
    ];
    foreach ($prefixes as $prefix => $baseDir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            continue;
        }
        $relative = str_replace('\\', '/', substr($class, $len));
        $file = $baseDir . $relative . '.php';
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});

// Load environment variables
Config\loadEnv(__DIR__ . '/.env');

// Create router and register routes
$router = new Router();

$router->add('GET', '/login', [App\Controllers\AuthController::class, 'login']);
$router->add('POST', '/login', [App\Controllers\AuthController::class, 'login']);
$router->add('GET', '/logout', [App\Controllers\AuthController::class, 'logout']);

$router->add('GET', '/app', [App\Controllers\DashboardController::class, 'index']);

$router->add('GET', '/tickets', [App\Controllers\TicketController::class, 'index']);

$router->add('GET', '/assets', [App\Controllers\AssetController::class, 'index']);
$router->add('GET', '/assets/download', [App\Controllers\AssetController::class, 'download']);

$router->add('GET', '/portal', [App\Controllers\PortalController::class, 'index']);

$router->add('GET', '/content', [App\Controllers\ContentController::class, 'index']);

// Dispatch based on current request
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$router->dispatch($method, $uri);