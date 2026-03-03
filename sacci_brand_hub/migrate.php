<?php

/**
 * Simple migration runner for Sacci Brand Hub / Brand Hub.
 *
 * Usage:
 *   php migrate.php
 *
 * This runner creates a schema_migrations table, executes pending migration
 * files in numeric order, and records each successful file exactly once.
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/Database.php';

use Core\Database;

Database::init();
$pdo = Database::getConnection();

$pdo->exec(
    "CREATE TABLE IF NOT EXISTS schema_migrations (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        filename VARCHAR(255) NOT NULL UNIQUE,
        executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
);

$executed = [];
$stmt = $pdo->query('SELECT filename FROM schema_migrations');
foreach ($stmt->fetchAll() as $row) {
    $executed[$row['filename']] = true;
}

$migrationFiles = glob(__DIR__ . '/migrations/*.php');
sort($migrationFiles, SORT_NATURAL);

$insertStmt = $pdo->prepare(
    'INSERT INTO schema_migrations (filename) VALUES (:filename)'
);

$ranCount = 0;

foreach ($migrationFiles as $migrationFile) {
    $filename = basename($migrationFile);

    if (isset($executed[$filename])) {
        echo "Skipping {$filename} (already applied)", PHP_EOL;
        continue;
    }

    echo "Running {$filename}", PHP_EOL;
    require $migrationFile;
    $insertStmt->execute(['filename' => $filename]);
    $ranCount++;
}

echo "Completed. Applied {$ranCount} new migration(s).", PHP_EOL;
