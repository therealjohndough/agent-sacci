#!/usr/bin/env php
<?php

/**
 * CLI Installer for Sacci Brand Hub.
 *
 * This script is the command-line equivalent of the browser-based installer
 * at install/index.php. It must only be run from the terminal — never via
 * a web server.
 *
 * Usage:
 *   php bin/install.php
 *
 * On production environments (APP_ENV=production) you must add --force:
 *   php bin/install.php --force
 *
 * The script will:
 *   1. Verify it is running from the CLI.
 *   2. Load the .env file.
 *   3. Run all migrations in numeric order.
 *   4. Seed initial data.
 *   5. Remove the install/ directory.
 */

// ---------------------------------------------------------------------------
// 1. CLI-only guard
// ---------------------------------------------------------------------------
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo '403 Forbidden — This script must be run from the command line.' . PHP_EOL;
    exit(1);
}

// ---------------------------------------------------------------------------
// 2. Parse CLI arguments
// ---------------------------------------------------------------------------
$args  = array_slice($argv ?? [], 1);
$force = in_array('--force', $args, true);

// ---------------------------------------------------------------------------
// 3. Bootstrap — load config helpers and environment
// ---------------------------------------------------------------------------
$appRoot = dirname(__DIR__);

require_once $appRoot . '/config/config.php';

Config\loadEnv($appRoot . '/.env');

// ---------------------------------------------------------------------------
// 4. Production safety gate
// ---------------------------------------------------------------------------
$appEnv = Config\env('APP_ENV', '');
if ($appEnv === 'production' && !$force) {
    echo 'ERROR: APP_ENV is set to "production".' . PHP_EOL;
    echo 'Re-run with --force to proceed:' . PHP_EOL;
    echo '  php bin/install.php --force' . PHP_EOL;
    exit(1);
}

// ---------------------------------------------------------------------------
// 5. Load the Database class and establish a connection
// ---------------------------------------------------------------------------
require_once $appRoot . '/core/Database.php';

use Core\Database;

Database::init();
$pdo = Database::getConnection();

// ---------------------------------------------------------------------------
// 6. Run migrations in numeric order
// ---------------------------------------------------------------------------
$migrationsDir = $appRoot . '/migrations';
$migrationFiles = glob($migrationsDir . '/*.php');
if ($migrationFiles === false) {
    echo 'ERROR: Could not read migrations/ directory.' . PHP_EOL;
    exit(1);
}
sort($migrationFiles);

echo 'Running migrations...' . PHP_EOL;
foreach ($migrationFiles as $file) {
    $name = basename($file);
    echo "  -> {$name} ... ";
    // Each migration script bootstraps its own requires and runs inline.
    // We capture output so it does not interleave with our messages.
    ob_start();
    require $file;
    $output = trim(ob_get_clean());
    echo ($output !== '' ? $output : 'done') . PHP_EOL;
}

// ---------------------------------------------------------------------------
// 7. Remove the install/ directory
// ---------------------------------------------------------------------------
$installDir = $appRoot . '/install';
if (is_dir($installDir)) {
    echo 'Removing install/ directory...' . PHP_EOL;
    if (!removeDirectory($installDir)) {
        echo 'WARNING: Could not fully remove ' . $installDir . PHP_EOL;
        echo '         Please delete it manually before going to production.' . PHP_EOL;
    } else {
        echo 'install/ directory removed.' . PHP_EOL;
    }
} else {
    echo 'install/ directory not found — skipping removal.' . PHP_EOL;
}

echo PHP_EOL . 'Installation complete.' . PHP_EOL;
exit(0);

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

/**
 * Recursively remove a directory and all its contents.
 */
function removeDirectory(string $dir): bool
{
    if (!is_dir($dir)) {
        return true;
    }
    $items = scandir($dir);
    if ($items === false) {
        return false;
    }
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            if (!removeDirectory($path)) {
                return false;
            }
        } else {
            if (!unlink($path)) {
                return false;
            }
        }
    }
    return rmdir($dir);
}
