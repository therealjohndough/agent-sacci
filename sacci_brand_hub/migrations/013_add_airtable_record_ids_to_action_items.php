<?php

/**
 * Add Airtable record tracking to action items for idempotent sync.
 */

use Core\Database;

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';

Database::init();
$pdo = Database::getConnection();

$pdo->exec(
    "ALTER TABLE action_items
        ADD COLUMN IF NOT EXISTS airtable_record_id VARCHAR(32) NULL UNIQUE AFTER created_by_user_id;"
);

echo "Migration 013 completed successfully.";
