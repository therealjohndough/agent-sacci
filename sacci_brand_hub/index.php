<?php

// Front controller

// Start session first
session_start();

// Manually require the config file that defines Config\loadEnv()
require_once __DIR__ . '/config/config.php';

// Autoloader for PSR-4 style classes
spl_autoload_register(function ($class) {
    $prefixes = [
        'App\\'    => __DIR__ . '/app/',
        'Core\\'   => __DIR__ . '/core/',
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

// Base path for subfolder install — auto-detected, but overridable via .env.
define('APP_BASE', Config\appBase($_SERVER['SCRIPT_NAME'] ?? ''));

// Baseline hardening headers for auth and internal tooling pages.
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:; form-action 'self'; base-uri 'self'; frame-ancestors 'self'");

// Create router and register routes
$router = new Core\Router();

$router->add('GET',  '/',                   [App\Controllers\AuthController::class, 'login']);
$router->add('GET',  '/login',              [App\Controllers\AuthController::class, 'login']);
$router->add('POST', '/login',              [App\Controllers\AuthController::class, 'login']);
$router->add('GET',  '/logout',             [App\Controllers\AuthController::class, 'logout']);

$router->add('GET',  '/app',                [App\Controllers\DashboardController::class, 'index']);
$router->add('GET',  '/dashboard/executive',[App\Controllers\ExecutiveDashboardController::class, 'index']);

$router->add('GET',  '/actions',            [App\Controllers\ActionController::class, 'index']);
$router->add('GET',  '/actions/new',        [App\Controllers\ActionController::class, 'create']);
$router->add('GET',  '/actions/edit',       [App\Controllers\ActionController::class, 'edit']);
$router->add('POST', '/actions',            [App\Controllers\ActionController::class, 'store']);
$router->add('POST', '/actions/archive',    [App\Controllers\ActionController::class, 'archive']);
$router->add('POST', '/actions/sync-airtable', [App\Controllers\ActionController::class, 'syncFromAirtable']);
$router->add('POST', '/actions/update',     [App\Controllers\ActionController::class, 'update']);
$router->add('GET',  '/tickets',            [App\Controllers\TicketController::class, 'index']);

$router->add('GET',  '/assets',             [App\Controllers\AssetController::class, 'index']);
$router->add('GET',  '/assets/download',    [App\Controllers\AssetController::class, 'download']);
$router->add('GET',  '/departments',        [App\Controllers\DepartmentController::class, 'index']);
$router->add('GET',  '/documents',          [App\Controllers\DocumentController::class, 'index']);
$router->add('GET',  '/documents/new',      [App\Controllers\DocumentController::class, 'create']);
$router->add('GET',  '/documents/edit',     [App\Controllers\DocumentController::class, 'edit']);
$router->add('POST', '/documents',          [App\Controllers\DocumentController::class, 'store']);
$router->add('POST', '/documents/archive',  [App\Controllers\DocumentController::class, 'archive']);
$router->add('POST', '/documents/update',   [App\Controllers\DocumentController::class, 'update']);
$router->add('GET',  '/meetings',           [App\Controllers\MeetingController::class, 'index']);
$router->add('GET',  '/meetings/new',       [App\Controllers\MeetingController::class, 'create']);
$router->add('GET',  '/meetings/edit',      [App\Controllers\MeetingController::class, 'edit']);
$router->add('POST', '/meetings',           [App\Controllers\MeetingController::class, 'store']);
$router->add('POST', '/meetings/archive',   [App\Controllers\MeetingController::class, 'archive']);
$router->add('POST', '/meetings/update',    [App\Controllers\MeetingController::class, 'update']);
$router->add('GET',  '/people',             [App\Controllers\PeopleController::class, 'index']);
$router->add('GET',  '/reports',            [App\Controllers\ReportController::class, 'index']);
$router->add('GET',  '/reports/new',        [App\Controllers\ReportController::class, 'create']);
$router->add('GET',  '/reports/edit',       [App\Controllers\ReportController::class, 'edit']);
$router->add('POST', '/reports',            [App\Controllers\ReportController::class, 'store']);
$router->add('POST', '/reports/archive',    [App\Controllers\ReportController::class, 'archive']);
$router->add('GET',  '/reports/entries/edit',[App\Controllers\ReportController::class, 'editEntry']);
$router->add('POST', '/reports/entries',    [App\Controllers\ReportController::class, 'storeEntry']);
$router->add('POST', '/reports/entries/delete',[App\Controllers\ReportController::class, 'deleteEntry']);
$router->add('POST', '/reports/entries/update',[App\Controllers\ReportController::class, 'updateEntry']);
$router->add('POST', '/reports/update',     [App\Controllers\ReportController::class, 'update']);
$router->add('GET',  '/search',             [App\Controllers\SearchController::class, 'index']);

$router->add('GET',  '/portal',             [App\Controllers\PortalController::class, 'index']);

$router->add('GET',  '/content',            [App\Controllers\ContentController::class, 'index']);

// Dispatch based on current request
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$base   = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
if ($base !== '' && str_starts_with($uri, $base)) {
    $uri = substr($uri, strlen($base)) ?: '/';
}
$router->dispatch($method, $uri);
