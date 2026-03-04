<?php

/**
 * Migration 017: extend strains, batches, products, and coas tables
 * to support COA cannabinoid/terpene data, mood tagging, and parse auditing.
 *
 * Run via CLI:
 *   php migrations/017_add_coa_cannabinoid_fields.php
 *
 * Uses individual ADD COLUMN statements (not IF NOT EXISTS, which is
 * MariaDB-only) and skips columns that already exist.
 */

use Core\Database;

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';

Database::init();
$pdo = Database::getConnection();
$db  = \Config\env('DB_DATABASE');

/**
 * Return the set of column names already present in a table.
 *
 * @return array<string>
 */
function existingColumns(\PDO $pdo, string $db, string $table): array
{
    $stmt = $pdo->prepare(
        'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
         WHERE TABLE_SCHEMA = :db AND TABLE_NAME = :tbl'
    );
    $stmt->execute(['db' => $db, 'tbl' => $table]);
    return array_map('strtolower', $stmt->fetchAll(\PDO::FETCH_COLUMN));
}

/**
 * Run ALTER TABLE … ADD COLUMN, skipping already-existing columns.
 *
 * @param array<array{table:string, col:string, def:string, after:string|null}> $columns
 */
function addColumns(\PDO $pdo, string $db, array $columns): void
{
    $byTable = [];
    foreach ($columns as $c) {
        $byTable[$c['table']][] = $c;
    }

    foreach ($byTable as $table => $cols) {
        $existing = existingColumns($pdo, $db, $table);
        $needed   = [];
        foreach ($cols as $c) {
            if (!in_array(strtolower($c['col']), $existing, true)) {
                $after    = $c['after'] ? ' AFTER ' . $c['after'] : '';
                $needed[] = 'ADD COLUMN ' . $c['col'] . ' ' . $c['def'] . $after;
            }
        }
        if (empty($needed)) {
            echo "  {$table}: all columns already present, skipping.\n";
            continue;
        }
        $sql = 'ALTER TABLE ' . $table . ' ' . implode(', ', $needed);
        $pdo->exec($sql);
        echo "  {$table}: added " . count($needed) . " column(s).\n";
    }
}

// ---------------------------------------------------------------------------
// Strain-level reference fields (from strain library CSV)
// ---------------------------------------------------------------------------
addColumns($pdo, $db, [
    ['table' => 'strains', 'col' => 'thc_ref',    'def' => 'DECIMAL(5,2)  NULL', 'after' => 'description'],
    ['table' => 'strains', 'col' => 'cbg_ref',    'def' => 'DECIMAL(5,2)  NULL', 'after' => 'thc_ref'],
    ['table' => 'strains', 'col' => 'cbn_ref',    'def' => 'DECIMAL(5,2)  NULL', 'after' => 'cbg_ref'],
    ['table' => 'strains', 'col' => 'terp_1_ref', 'def' => 'VARCHAR(80)   NULL', 'after' => 'cbn_ref'],
    ['table' => 'strains', 'col' => 'terp_2_ref', 'def' => 'VARCHAR(80)   NULL', 'after' => 'terp_1_ref'],
    ['table' => 'strains', 'col' => 'terp_3_ref', 'def' => 'VARCHAR(80)   NULL', 'after' => 'terp_2_ref'],
    ['table' => 'strains', 'col' => 'awards',     'def' => 'TEXT          NULL', 'after' => 'terp_3_ref'],
]);

// ---------------------------------------------------------------------------
// Per-batch COA breakdown fields (populated from COA PDFs)
// ---------------------------------------------------------------------------
addColumns($pdo, $db, [
    ['table' => 'batches', 'col' => 'cbg_percent',       'def' => 'DECIMAL(5,2)  NULL', 'after' => 'cbd_percent'],
    ['table' => 'batches', 'col' => 'cbn_percent',       'def' => 'DECIMAL(5,2)  NULL', 'after' => 'cbg_percent'],
    ['table' => 'batches', 'col' => 'terp_total',        'def' => 'DECIMAL(5,2)  NULL', 'after' => 'cbn_percent'],
    ['table' => 'batches', 'col' => 'terp_1_name',       'def' => 'VARCHAR(80)   NULL', 'after' => 'terp_total'],
    ['table' => 'batches', 'col' => 'terp_1_pct',        'def' => 'DECIMAL(5,2)  NULL', 'after' => 'terp_1_name'],
    ['table' => 'batches', 'col' => 'terp_2_name',       'def' => 'VARCHAR(80)   NULL', 'after' => 'terp_1_pct'],
    ['table' => 'batches', 'col' => 'terp_2_pct',        'def' => 'DECIMAL(5,2)  NULL', 'after' => 'terp_2_name'],
    ['table' => 'batches', 'col' => 'terp_3_name',       'def' => 'VARCHAR(80)   NULL', 'after' => 'terp_2_pct'],
    ['table' => 'batches', 'col' => 'terp_3_pct',        'def' => 'DECIMAL(5,2)  NULL', 'after' => 'terp_3_name'],
    ['table' => 'batches', 'col' => 'other_cannabinoids','def' => 'VARCHAR(200)  NULL', 'after' => 'terp_3_pct'],
    ['table' => 'batches', 'col' => 'mood_tag',          'def' => 'VARCHAR(50)   NULL', 'after' => 'other_cannabinoids'],
]);

// ---------------------------------------------------------------------------
// Product catalog additions
// ---------------------------------------------------------------------------
addColumns($pdo, $db, [
    ['table' => 'products', 'col' => 'mood_tag',    'def' => 'VARCHAR(50)  NULL', 'after' => 'description'],
    ['table' => 'products', 'col' => 'notes_label', 'def' => 'VARCHAR(100) NULL', 'after' => 'mood_tag'],
]);

// ---------------------------------------------------------------------------
// COA parse audit fields
// ---------------------------------------------------------------------------
addColumns($pdo, $db, [
    ['table' => 'coas', 'col' => 'parsed_at',    'def' => 'DATETIME NULL',                             'after' => 'notes'],
    ['table' => 'coas', 'col' => 'parse_raw',    'def' => 'TEXT NULL',                                 'after' => 'parsed_at'],
    ['table' => 'coas', 'col' => 'parse_source', 'def' => "ENUM('manual','ai') DEFAULT 'manual' NULL", 'after' => 'parse_raw'],
]);

echo "Migration 017 completed successfully.\n";
