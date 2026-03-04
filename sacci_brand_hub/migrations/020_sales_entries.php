<?php

/**
 * Migration 020: Sales entries table for manual sell-through tracking.
 *
 * Creates a lightweight table for logging units sold per product per period.
 * No FK constraint on products so this migration is independent of 014.
 *
 * Run via CLI:
 *   php migrations/020_sales_entries.php
 */

use Core\Database;

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';

Database::init();
$pdo = Database::getConnection();

$pdo->exec("CREATE TABLE IF NOT EXISTS sales_entries (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    reporting_date DATE NOT NULL,
    units_sold INT UNSIGNED NOT NULL DEFAULT 0,
    revenue_cents INT UNSIGNED NULL,
    channel VARCHAR(100) NULL,
    notes TEXT NULL,
    recorded_by INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_sales_product (product_id),
    INDEX idx_sales_date (reporting_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

echo "  sales_entries: table ensured.\n";
echo "Migration 020 completed successfully.\n";
