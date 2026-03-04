<?php

/**
 * Migration 018: Extend tickets table for structured marketing requests.
 *
 * - Converts request_type ENUM → VARCHAR(50) so any type string can be stored.
 * - Adds linked_strain_id and linked_product_id foreign-key columns.
 * - Converts priority ENUM → VARCHAR(20) for consistent lowercase values.
 *
 * Run via CLI:
 *   php migrations/018_marketing_requests.php
 *
 * Uses INFORMATION_SCHEMA pattern (MySQL-safe, idempotent).
 */

use Core\Database;

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';

Database::init();
$pdo = Database::getConnection();
$db  = \Config\env('DB_DATABASE');

function existingColumns018(\PDO $pdo, string $db, string $table): array
{
    $stmt = $pdo->prepare(
        'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
         WHERE TABLE_SCHEMA = :db AND TABLE_NAME = :tbl'
    );
    $stmt->execute(['db' => $db, 'tbl' => $table]);
    return array_map('strtolower', $stmt->fetchAll(\PDO::FETCH_COLUMN));
}

function columnType018(\PDO $pdo, string $db, string $table, string $column): string
{
    $stmt = $pdo->prepare(
        'SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS
         WHERE TABLE_SCHEMA = :db AND TABLE_NAME = :tbl AND LOWER(COLUMN_NAME) = LOWER(:col)
         LIMIT 1'
    );
    $stmt->execute(['db' => $db, 'tbl' => $table, 'col' => $column]);
    return (string) $stmt->fetchColumn();
}

$existing = existingColumns018($pdo, $db, 'tickets');
$alters   = [];

// Convert request_type from ENUM to VARCHAR(50) if it's currently an ENUM
if (in_array('request_type', $existing, true)) {
    $type = columnType018($pdo, $db, 'tickets', 'request_type');
    if (strtolower($type) === 'enum') {
        $alters[] = 'MODIFY COLUMN request_type VARCHAR(50) NULL';
    }
} else {
    $alters[] = 'ADD COLUMN request_type VARCHAR(50) NULL AFTER title';
}

// Convert priority from ENUM to VARCHAR(20) if it's currently an ENUM
if (in_array('priority', $existing, true)) {
    $type = columnType018($pdo, $db, 'tickets', 'priority');
    if (strtolower($type) === 'enum') {
        $alters[] = 'MODIFY COLUMN priority VARCHAR(20) NULL';
    }
} else {
    $alters[] = "ADD COLUMN priority VARCHAR(20) DEFAULT 'normal' AFTER request_type";
}

// Add linked_strain_id
if (!in_array('linked_strain_id', $existing, true)) {
    $alters[] = 'ADD COLUMN linked_strain_id INT UNSIGNED NULL';
}

// Add linked_product_id
if (!in_array('linked_product_id', $existing, true)) {
    $alters[] = 'ADD COLUMN linked_product_id INT UNSIGNED NULL';
}

if (empty($alters)) {
    echo "  tickets: already up to date, skipping.\n";
} else {
    $sql = 'ALTER TABLE tickets ' . implode(', ', $alters);
    $pdo->exec($sql);
    echo '  tickets: applied ' . count($alters) . " change(s).\n";
}

// ---------------------------------------------------------------------------
// assets table: add linked_product_id if not present
// ---------------------------------------------------------------------------
$existingAssets = existingColumns018($pdo, $db, 'assets');
$assetAlters    = [];

if (!in_array('linked_product_id', $existingAssets, true)) {
    $assetAlters[] = 'ADD COLUMN linked_product_id INT UNSIGNED NULL';
}

if (empty($assetAlters)) {
    echo "  assets: already up to date, skipping.\n";
} else {
    $pdo->exec('ALTER TABLE assets ' . implode(', ', $assetAlters));
    echo '  assets: applied ' . count($assetAlters) . " change(s).\n";
}

echo "Migration 018 completed successfully.\n";
