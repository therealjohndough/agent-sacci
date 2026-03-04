<?php

/**
 * Migration 019: People directory foundation (MySQL-safe).
 *
 * Equivalent to migrations 003 + 004 but uses INFORMATION_SCHEMA for
 * idempotent ALTER TABLE — compatible with MySQL 8.x (no IF NOT EXISTS).
 *
 * - Creates departments and user_department_assignments tables.
 * - Adds job_title, profile_summary, is_active to users if absent.
 * - Seeds department records.
 *
 * Run via CLI:
 *   php migrations/019_people_directory.php
 */

use Core\Database;

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';

Database::init();
$pdo = Database::getConnection();
$db  = \Config\env('DB_DATABASE');

// ---------------------------------------------------------------------------
// departments table
// ---------------------------------------------------------------------------
$pdo->exec("CREATE TABLE IF NOT EXISTS departments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "  departments: table ensured.\n";

// ---------------------------------------------------------------------------
// user_department_assignments table
// ---------------------------------------------------------------------------
$pdo->exec("CREATE TABLE IF NOT EXISTS user_department_assignments (
    user_id INT UNSIGNED NOT NULL,
    department_id INT UNSIGNED NOT NULL,
    assignment_type ENUM('primary','secondary') DEFAULT 'primary',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, department_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "  user_department_assignments: table ensured.\n";

// ---------------------------------------------------------------------------
// users table: add missing columns via INFORMATION_SCHEMA
// ---------------------------------------------------------------------------
function existingColumns019(\PDO $pdo, string $db, string $table): array
{
    $stmt = $pdo->prepare(
        'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
         WHERE TABLE_SCHEMA = :db AND TABLE_NAME = :tbl'
    );
    $stmt->execute(['db' => $db, 'tbl' => $table]);
    return array_map('strtolower', $stmt->fetchAll(\PDO::FETCH_COLUMN));
}

$userCols  = existingColumns019($pdo, $db, 'users');
$userAlters = [];

if (!in_array('job_title', $userCols, true)) {
    $userAlters[] = 'ADD COLUMN job_title VARCHAR(150) NULL AFTER name';
}
if (!in_array('profile_summary', $userCols, true)) {
    $userAlters[] = 'ADD COLUMN profile_summary TEXT NULL AFTER job_title';
}
if (!in_array('is_active', $userCols, true)) {
    $userAlters[] = 'ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER organization_id';
}

if (empty($userAlters)) {
    echo "  users: already up to date, skipping.\n";
} else {
    $pdo->exec('ALTER TABLE users ' . implode(', ', $userAlters));
    echo '  users: applied ' . count($userAlters) . " column(s).\n";
}

// ---------------------------------------------------------------------------
// Seed departments (idempotent via ON DUPLICATE KEY)
// ---------------------------------------------------------------------------
$departments = [
    ['name' => 'Executive',          'slug' => 'executive',        'description' => 'Leadership, approvals, and company-level priorities.'],
    ['name' => 'Design and Marketing','slug' => 'design-marketing', 'description' => 'Brand design, campaign execution, and creative operations.'],
    ['name' => 'Inventory',           'slug' => 'inventory',        'description' => 'Inventory oversight, system administration, and stock operations.'],
    ['name' => 'Sales',               'slug' => 'sales',            'description' => 'Revenue operations, retailer relationships, and commercial reporting.'],
    ['name' => 'Compliance / Metrc',  'slug' => 'compliance-metrc', 'description' => 'Metrc workflows, compliance records, and audit readiness.'],
    ['name' => 'Cultivation',         'slug' => 'cultivation',      'description' => 'Grow operations, production notes, and product insight.'],
    ['name' => 'Deliveries',          'slug' => 'deliveries',       'description' => 'Delivery logistics, routing, and fulfillment issue management.'],
];

$deptStmt = $pdo->prepare(
    'INSERT INTO departments (name, slug, description)
     VALUES (:name, :slug, :description)
     ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description)'
);
foreach ($departments as $d) {
    $deptStmt->execute($d);
}
echo "  departments: " . count($departments) . " record(s) seeded.\n";

echo "Migration 019 completed successfully.\n";
