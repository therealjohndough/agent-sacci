<?php

/**
 * Migration script to add structured internal reports.
 *
 * Reports hold recurring business snapshots for sales, inventory,
 * compliance, cultivation, delivery, and executive review.
 */

use Core\Database;

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';

Database::init();
$pdo = Database::getConnection();

$sql = [
    "CREATE TABLE IF NOT EXISTS reports (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        report_type VARCHAR(100) NOT NULL DEFAULT 'general',
        department_id INT UNSIGNED NULL,
        owner_user_id INT UNSIGNED NULL,
        reporting_period_start DATE NULL,
        reporting_period_end DATE NULL,
        status ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
        source_url VARCHAR(255) NULL,
        summary TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
        FOREIGN KEY (owner_user_id) REFERENCES users(id) ON DELETE SET NULL,
        INDEX idx_reports_department (department_id),
        INDEX idx_reports_owner (owner_user_id),
        INDEX idx_reports_status (status),
        INDEX idx_reports_type (report_type),
        INDEX idx_reports_period_start (reporting_period_start),
        INDEX idx_reports_period_end (reporting_period_end)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS report_entries (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        report_id INT UNSIGNED NOT NULL,
        metric_key VARCHAR(100) NOT NULL,
        metric_label VARCHAR(150) NOT NULL,
        metric_value VARCHAR(255) NULL,
        metric_unit VARCHAR(50) NULL,
        notes TEXT NULL,
        sort_order INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE,
        INDEX idx_report_entries_report (report_id),
        INDEX idx_report_entries_sort (report_id, sort_order)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
];

foreach ($sql as $query) {
    $pdo->exec($query);
}

echo "Migration 011 completed successfully.";
