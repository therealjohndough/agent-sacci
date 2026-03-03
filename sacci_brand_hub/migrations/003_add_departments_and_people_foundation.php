<?php

/**
 * Migration script to add department and people-directory foundations.
 *
 * This extends the existing user model so the app can represent internal
 * department ownership without changing the current authentication flow.
 */

use Core\Database;

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';

Database::init();
$pdo = Database::getConnection();

$sql = [
    "CREATE TABLE IF NOT EXISTS departments (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        slug VARCHAR(100) NOT NULL UNIQUE,
        description TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS user_department_assignments (
        user_id INT UNSIGNED NOT NULL,
        department_id INT UNSIGNED NOT NULL,
        assignment_type ENUM('primary','secondary') DEFAULT 'primary',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (user_id, department_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "ALTER TABLE users
        ADD COLUMN IF NOT EXISTS job_title VARCHAR(150) NULL AFTER name,
        ADD COLUMN IF NOT EXISTS profile_summary TEXT NULL AFTER job_title,
        ADD COLUMN IF NOT EXISTS is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER organization_id;"
];

foreach ($sql as $query) {
    $pdo->exec($query);
}

echo "Migration 003 completed successfully.";
