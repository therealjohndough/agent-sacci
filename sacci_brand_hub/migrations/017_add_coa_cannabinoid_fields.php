<?php

/**
 * Migration 017: extend strains, batches, products, and coas tables
 * to support COA cannabinoid/terpene data, mood tagging, and parse auditing.
 *
 * Run via CLI:
 *   php migrations/017_add_coa_cannabinoid_fields.php
 */

use Core\Database;

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';

Database::init();
$pdo = Database::getConnection();

$statements = [

    // Strain-level reference fields (from strain library CSV)
    "ALTER TABLE strains
        ADD COLUMN IF NOT EXISTS thc_ref    DECIMAL(5,2)  NULL AFTER description,
        ADD COLUMN IF NOT EXISTS cbg_ref    DECIMAL(5,2)  NULL AFTER thc_ref,
        ADD COLUMN IF NOT EXISTS cbn_ref    DECIMAL(5,2)  NULL AFTER cbg_ref,
        ADD COLUMN IF NOT EXISTS terp_1_ref VARCHAR(80)   NULL AFTER cbn_ref,
        ADD COLUMN IF NOT EXISTS terp_2_ref VARCHAR(80)   NULL AFTER terp_1_ref,
        ADD COLUMN IF NOT EXISTS terp_3_ref VARCHAR(80)   NULL AFTER terp_2_ref,
        ADD COLUMN IF NOT EXISTS awards     TEXT          NULL AFTER terp_3_ref",

    // Per-batch COA breakdown fields (populated from COA PDFs)
    "ALTER TABLE batches
        ADD COLUMN IF NOT EXISTS cbg_percent        DECIMAL(5,2)  NULL AFTER cbd_percent,
        ADD COLUMN IF NOT EXISTS cbn_percent        DECIMAL(5,2)  NULL AFTER cbg_percent,
        ADD COLUMN IF NOT EXISTS terp_total         DECIMAL(5,2)  NULL AFTER cbn_percent,
        ADD COLUMN IF NOT EXISTS terp_1_name        VARCHAR(80)   NULL AFTER terp_total,
        ADD COLUMN IF NOT EXISTS terp_1_pct         DECIMAL(5,2)  NULL AFTER terp_1_name,
        ADD COLUMN IF NOT EXISTS terp_2_name        VARCHAR(80)   NULL AFTER terp_1_pct,
        ADD COLUMN IF NOT EXISTS terp_2_pct         DECIMAL(5,2)  NULL AFTER terp_2_name,
        ADD COLUMN IF NOT EXISTS terp_3_name        VARCHAR(80)   NULL AFTER terp_2_pct,
        ADD COLUMN IF NOT EXISTS terp_3_pct         DECIMAL(5,2)  NULL AFTER terp_3_name,
        ADD COLUMN IF NOT EXISTS other_cannabinoids VARCHAR(200)  NULL AFTER terp_3_pct,
        ADD COLUMN IF NOT EXISTS mood_tag           VARCHAR(50)   NULL AFTER other_cannabinoids",

    // Product catalog additions (mood_tag auto-propagated from batch; notes_label for variant labels)
    "ALTER TABLE products
        ADD COLUMN IF NOT EXISTS mood_tag    VARCHAR(50)  NULL AFTER description,
        ADD COLUMN IF NOT EXISTS notes_label VARCHAR(100) NULL AFTER mood_tag",

    // COA parse audit fields
    "ALTER TABLE coas
        ADD COLUMN IF NOT EXISTS parsed_at    DATETIME NULL AFTER notes,
        ADD COLUMN IF NOT EXISTS parse_raw    TEXT     NULL AFTER parsed_at,
        ADD COLUMN IF NOT EXISTS parse_source ENUM('manual','ai') DEFAULT 'manual' AFTER parse_raw",
];

foreach ($statements as $sql) {
    $pdo->exec($sql);
}

echo "Migration 017 completed successfully.";
