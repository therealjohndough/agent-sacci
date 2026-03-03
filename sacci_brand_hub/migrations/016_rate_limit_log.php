<?php

/**
 * Migration: create the rate_limit_log table.
 *
 * This table is used by Core\RateLimiter to track per-IP attempt counts
 * for rate-limited actions (e.g. login). Rows are never soft-deleted —
 * the limiter queries by time window, and old rows can be pruned
 * periodically with a cron job or future migration if needed.
 *
 * Run via CLI:
 *   php migrations/016_rate_limit_log.php
 */

use Core\Database;

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';

Database::init();
$pdo = Database::getConnection();

$pdo->exec(
    "CREATE TABLE IF NOT EXISTS rate_limit_log (
        id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        ip_address   VARCHAR(45)  NOT NULL,
        action       VARCHAR(50)  NOT NULL,
        attempted_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_rate_limit_lookup (ip_address, action, attempted_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
);

echo "Migration 016 completed successfully.";
