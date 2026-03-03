<?php

/**
 * Migration script to add internal document management records.
 *
 * Documents are the knowledge-base layer for SOPs, policies, playbooks,
 * and internal reference material.
 */

use Core\Database;

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';

Database::init();
$pdo = Database::getConnection();

$sql = [
    "CREATE TABLE IF NOT EXISTS documents (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        document_type VARCHAR(100) NOT NULL DEFAULT 'reference',
        department_id INT UNSIGNED NULL,
        owner_user_id INT UNSIGNED NULL,
        status ENUM('draft','active','archived') NOT NULL DEFAULT 'draft',
        source_url VARCHAR(255) NULL,
        content LONGTEXT NOT NULL,
        version_label VARCHAR(50) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
        FOREIGN KEY (owner_user_id) REFERENCES users(id) ON DELETE SET NULL,
        INDEX idx_documents_department (department_id),
        INDEX idx_documents_owner (owner_user_id),
        INDEX idx_documents_status (status),
        INDEX idx_documents_type (document_type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
];

foreach ($sql as $query) {
    $pdo->exec($query);
}

echo "Migration 009 completed successfully.";
