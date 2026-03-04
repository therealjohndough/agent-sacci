<?php

// Front controller

// Start session first
session_start();

// Manually require the config file that defines Config\loadEnv()
require_once __DIR__ . '/config/config.php';

// ---------------------------------------------------------------------------
// /install/ route gating — block access on non-local environments.
// This check runs before the autoloader and full env load so it is always
// enforced regardless of whether the application has been configured yet.
// ---------------------------------------------------------------------------
(function (): void {
    $rawUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    // Strip subfolder prefix so the check works in both root and subfolder deploys.
    $scriptBase = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
    if ($scriptBase !== '' && str_starts_with($rawUri, $scriptBase)) {
        $rawUri = substr($rawUri, strlen($scriptBase)) ?: '/';
    }
    if (str_starts_with($rawUri, '/install/') || $rawUri === '/install') {
        // APP_ENV may not yet be loaded from .env — check native env first.
        $env = getenv('APP_ENV') ?: ($_ENV['APP_ENV'] ?? '');
        $allowed = in_array($env, ['local', 'development'], true);
        if (!$allowed) {
            http_response_code(403);
            echo '403 Forbidden — The installer is disabled in this environment.';
            exit;
        }
    }
})();

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

// ---------------------------------------------------------------------------
// storage/ web-accessibility check.
// Attempts to detect whether the storage directory is reachable via the web
// root. Logs a critical warning if so, and marks a flag in the session so the
// dashboard can surface a visible alert for admin users.
// ---------------------------------------------------------------------------
(function (): void {
    $storagePath = realpath(__DIR__ . '/storage') ?: '';
    $docRoot     = realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '';

    if ($storagePath === '' || $docRoot === '') {
        return;
    }

    // storage/ is web-accessible when it lives inside DOCUMENT_ROOT.
    if (str_starts_with($storagePath, $docRoot)) {
        // Suppress the warning if storage/.htaccess already denies all access.
        $htaccess = __DIR__ . '/storage/.htaccess';
        $htaccessContent = is_file($htaccess) ? file_get_contents($htaccess) : '';
        $protected = stripos($htaccessContent, 'Deny from all') !== false
                  || stripos($htaccessContent, 'Require all denied') !== false;

        if (!$protected) {
            error_log(
                'CRITICAL SECURITY WARNING: sacci_brand_hub/storage/ is inside the web '
                . 'document root (' . $docRoot . ') and may be directly accessible via HTTP. '
                . 'Move storage/ above the document root or add an .htaccess that denies all access.'
            );
            $_SERVER['_STORAGE_EXPOSED'] = true;
        }
    }
})();

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
$router->add('GET',  '/assets/upload',      [App\Controllers\AssetController::class, 'uploadForm']);
$router->add('POST', '/assets/upload',      [App\Controllers\AssetController::class, 'upload']);
$router->add('GET',  '/assets/download',    [App\Controllers\AssetController::class, 'download']);
$router->add('GET',  '/batches',            [App\Controllers\BatchController::class, 'index']);
$router->add('GET',  '/batches/new',        [App\Controllers\BatchController::class, 'create']);
$router->add('GET',  '/batches/edit',       [App\Controllers\BatchController::class, 'edit']);
$router->add('POST', '/batches',            [App\Controllers\BatchController::class, 'store']);
$router->add('POST', '/batches/archive',    [App\Controllers\BatchController::class, 'archive']);
$router->add('POST', '/batches/update',     [App\Controllers\BatchController::class, 'update']);
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
$router->add('GET',  '/strains',            [App\Controllers\StrainController::class, 'index']);
$router->add('GET',  '/strains/new',        [App\Controllers\StrainController::class, 'create']);
$router->add('GET',  '/strains/edit',       [App\Controllers\StrainController::class, 'edit']);
$router->add('GET',  '/strains/import',     [App\Controllers\StrainController::class, 'importForm']);
$router->add('POST', '/strains',            [App\Controllers\StrainController::class, 'store']);
$router->add('POST', '/strains/archive',    [App\Controllers\StrainController::class, 'archive']);
$router->add('POST', '/strains/update',     [App\Controllers\StrainController::class, 'update']);
$router->add('POST', '/strains/import',     [App\Controllers\StrainController::class, 'import']);

$router->add('GET',  '/products',           [App\Controllers\ProductController::class, 'index']);
$router->add('GET',  '/products/import',    [App\Controllers\ProductController::class, 'importForm']);
$router->add('POST', '/products/import',    [App\Controllers\ProductController::class, 'import']);

$router->add('GET',  '/batches/coa-upload', [App\Controllers\BatchController::class, 'coaUploadForm']);
$router->add('POST', '/batches/coa-upload', [App\Controllers\BatchController::class, 'coaUpload']);

$router->add('GET',  '/marketing',          [App\Controllers\MarketingController::class, 'index']);
$router->add('GET',  '/marketing/request',  [App\Controllers\MarketingController::class, 'requestForm']);
$router->add('POST', '/marketing/request',  [App\Controllers\MarketingController::class, 'submitRequest']);

$router->add('GET',  '/compliance',         [App\Controllers\ComplianceController::class, 'index']);

$router->add('GET',  '/sales',              [App\Controllers\SalesController::class, 'index']);
$router->add('GET',  '/sales/new',          [App\Controllers\SalesController::class, 'entryForm']);
$router->add('POST', '/sales',              [App\Controllers\SalesController::class, 'storeEntry']);

$router->add('GET',  '/campaigns',          [App\Controllers\CampaignController::class, 'index']);
$router->add('GET',  '/campaigns/new',      [App\Controllers\CampaignController::class, 'create']);
$router->add('GET',  '/campaigns/edit',     [App\Controllers\CampaignController::class, 'edit']);
$router->add('POST', '/campaigns',          [App\Controllers\CampaignController::class, 'store']);
$router->add('POST', '/campaigns/update',   [App\Controllers\CampaignController::class, 'update']);
$router->add('POST', '/campaigns/archive',  [App\Controllers\CampaignController::class, 'archive']);

$router->add('GET',  '/portal',             [App\Controllers\PortalController::class, 'index']);

$router->add('GET',  '/content',            [App\Controllers\ContentController::class, 'index']);

$router->add('GET',  '/admin/test-mail',    [App\Controllers\AdminController::class, 'testMail']);
$router->add('GET',  '/admin/users',        [App\Controllers\AdminController::class, 'users']);
$router->add('GET',  '/admin/users/roles',  [App\Controllers\AdminController::class, 'editUserRoles']);
$router->add('POST', '/admin/users/roles',  [App\Controllers\AdminController::class, 'updateUserRoles']);

// Dispatch based on current request
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$base   = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
if ($base !== '' && str_starts_with($uri, $base)) {
    $uri = substr($uri, strlen($base)) ?: '/';
}
$router->dispatch($method, $uri);
