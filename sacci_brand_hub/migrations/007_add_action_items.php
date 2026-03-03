<?php

/**
 * Migration script to add cross-module action item tracking.
 *
 * Action items are the operational follow-up layer for meetings, reports,
 * documents, and tickets.
 */

use Core\Database;

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';

Database::init();
$pdo = Database::getConnection();

$sql = [
    "CREATE TABLE IF NOT EXISTS action_items (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        details TEXT NULL,
        status ENUM('open','in_progress','blocked','done','archived') NOT NULL DEFAULT 'open',
        priority ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
        department_id INT UNSIGNED NULL,
        owner_user_id INT UNSIGNED NULL,
        created_by_user_id INT UNSIGNED NULL,
        source_type ENUM('meeting','report','document','ticket','manual') NOT NULL DEFAULT 'manual',
        source_id INT UNSIGNED NULL,
        due_date DATE NULL,
        completed_at DATETIME NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
        FOREIGN KEY (owner_user_id) REFERENCES users(id) ON DELETE SET NULL,
        FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
        INDEX idx_action_items_status (status),
        INDEX idx_action_items_priority (priority),
        INDEX idx_action_items_department (department_id),
        INDEX idx_action_items_owner (owner_user_id),
        INDEX idx_action_items_source (source_type, source_id),
        INDEX idx_action_items_due_date (due_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
];

foreach ($sql as $query) {
    $pdo->exec($query);
}

echo "Migration 007 completed successfully.";
