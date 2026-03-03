<?php

/**
 * Migration script to add meeting and decision tracking.
 *
 * This establishes the first core "company brain" content type:
 * structured meeting records with attendees and durable decisions.
 */

use Core\Database;

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';

Database::init();
$pdo = Database::getConnection();

$sql = [
    "CREATE TABLE IF NOT EXISTS meetings (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        meeting_type VARCHAR(100) NOT NULL DEFAULT 'general',
        department_id INT UNSIGNED NULL,
        owner_user_id INT UNSIGNED NULL,
        scheduled_for DATETIME NULL,
        occurred_at DATETIME NULL,
        status ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
        summary TEXT NULL,
        notes LONGTEXT NOT NULL,
        source_url VARCHAR(255) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
        FOREIGN KEY (owner_user_id) REFERENCES users(id) ON DELETE SET NULL,
        INDEX idx_meetings_department (department_id),
        INDEX idx_meetings_owner (owner_user_id),
        INDEX idx_meetings_status (status),
        INDEX idx_meetings_occurred_at (occurred_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS meeting_attendees (
        meeting_id INT UNSIGNED NOT NULL,
        user_id INT UNSIGNED NOT NULL,
        attendance_role ENUM('host','attendee','optional') NOT NULL DEFAULT 'attendee',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (meeting_id, user_id),
        FOREIGN KEY (meeting_id) REFERENCES meetings(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS meeting_decisions (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        meeting_id INT UNSIGNED NOT NULL,
        decision TEXT NOT NULL,
        owner_user_id INT UNSIGNED NULL,
        effective_date DATE NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (meeting_id) REFERENCES meetings(id) ON DELETE CASCADE,
        FOREIGN KEY (owner_user_id) REFERENCES users(id) ON DELETE SET NULL,
        INDEX idx_meeting_decisions_meeting (meeting_id),
        INDEX idx_meeting_decisions_owner (owner_user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
];

foreach ($sql as $query) {
    $pdo->exec($query);
}

echo "Migration 005 completed successfully.";
