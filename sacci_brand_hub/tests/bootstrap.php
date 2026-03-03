<?php

/**
 * PHPUnit bootstrap file for Sacci Brand Hub tests.
 *
 * Responsibilities:
 *   - Load the Composer autoloader (vendor/autoload.php)
 *   - Set APP_ENV to 'testing' so application code can behave accordingly
 *   - Define any constants the application expects at boot time
 *   - Load the Config namespace functions (config/config.php) manually,
 *     because the app uses function-based namespaces that Composer PSR-4
 *     cannot autoload automatically
 *   - Does NOT connect to any real database
 */

declare(strict_types=1);

// ── 1. Composer autoloader ────────────────────────────────────────────────────
// The vendor directory lives inside sacci_brand_hub/ alongside composer.json.
$vendorAutoload = __DIR__ . '/../vendor/autoload.php';

if (!file_exists($vendorAutoload)) {
    fwrite(
        STDERR,
        "ERROR: Composer autoloader not found at {$vendorAutoload}\n" .
        "Run `composer install` inside sacci_brand_hub/ first.\n"
    );
    exit(1);
}

require_once $vendorAutoload;

// ── 2. Load the Config namespace functions ────────────────────────────────────
// config/config.php defines Config\loadEnv(), Config\env(), Config\appBase(),
// and Config\appUrl() as free functions in the `Config` namespace.
// These are required by Core\Database and parts of the app bootstrap.
// Composer PSR-4 handles classes but not function-based namespace files, so
// we require this file explicitly.
require_once __DIR__ . '/../config/config.php';

// ── 3. Application environment ────────────────────────────────────────────────
putenv('APP_ENV=testing');
$_ENV['APP_ENV'] = 'testing';

// Prevent Database::init() from attempting a real MySQL connection by
// pointing DB settings at an obviously invalid host. Tests must mock the
// PDO layer instead of calling Database::getConnection() directly.
putenv('DB_HOST=127.0.0.1');
putenv('DB_PORT=0');
putenv('DB_DATABASE=sacci_test');
putenv('DB_USERNAME=test');
putenv('DB_PASSWORD=test');

// ── 4. Application constants ──────────────────────────────────────────────────
// index.php defines APP_BASE at boot time. Tests need it to be present so
// BaseController::redirect() does not trigger an undefined-constant warning.
if (!defined('APP_BASE')) {
    define('APP_BASE', '');
}

// ── 5. Session ────────────────────────────────────────────────────────────────
// Core\Auth and Core\Csrf read/write $_SESSION directly. Auth::logout() calls
// session_regenerate_id() which requires an active session even in CLI.
// Start a real (but memory-only) session so all session functions work.
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_cookies', '0');
    ini_set('session.use_only_cookies', '0');
    session_start();
}
if (!isset($_SESSION)) {
    $_SESSION = [];
}
