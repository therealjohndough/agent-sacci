<?php

/**
 * Seed sample internal documents for the company-brain knowledge base.
 */

use Core\Database;

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';

Database::init();
$pdo = Database::getConnection();

$departmentIds = [];
$departmentStmt = $pdo->query('SELECT id, slug FROM departments');
foreach ($departmentStmt->fetchAll() as $department) {
    $departmentIds[$department['slug']] = (int) $department['id'];
}

$userIds = [];
$userStmt = $pdo->query('SELECT id, email FROM users');
foreach ($userStmt->fetchAll() as $user) {
    $userIds[$user['email']] = (int) $user['id'];
}

$documents = [
    [
        'title' => 'Brand Hub Operating Principles',
        'slug' => 'brand-hub-operating-principles',
        'document_type' => 'playbook',
        'department_slug' => 'executive',
        'owner_email' => 'admin@houseofsacci.com',
        'status' => 'active',
        'source_url' => null,
        'version_label' => 'v1',
        'content' => "Brand Hub is the internal source of truth for operational memory.\n\nAll major decisions, meeting notes, and follow-up work should be tracked here before additional automation layers are added.",
    ],
    [
        'title' => 'Compliance Documentation Standard',
        'slug' => 'compliance-documentation-standard',
        'document_type' => 'sop',
        'department_slug' => 'compliance-metrc',
        'owner_email' => 'staff@houseofsacci.com',
        'status' => 'active',
        'source_url' => null,
        'version_label' => 'v1',
        'content' => "Store compliance-sensitive records in a consistent structure.\n\nEvery critical document should have a clear owner, a current version, and a stable location that can be referenced by the team.",
    ],
];

$documentStmt = $pdo->prepare(
    'INSERT INTO documents (
        title,
        slug,
        document_type,
        department_id,
        owner_user_id,
        status,
        source_url,
        content,
        version_label
    ) VALUES (
        :title,
        :slug,
        :document_type,
        :department_id,
        :owner_user_id,
        :status,
        :source_url,
        :content,
        :version_label
    )
    ON DUPLICATE KEY UPDATE
        title = VALUES(title),
        document_type = VALUES(document_type),
        department_id = VALUES(department_id),
        owner_user_id = VALUES(owner_user_id),
        status = VALUES(status),
        source_url = VALUES(source_url),
        content = VALUES(content),
        version_label = VALUES(version_label)'
);

foreach ($documents as $document) {
    $documentStmt->execute([
        'title' => $document['title'],
        'slug' => $document['slug'],
        'document_type' => $document['document_type'],
        'department_id' => $departmentIds[$document['department_slug']] ?? null,
        'owner_user_id' => $userIds[$document['owner_email']] ?? null,
        'status' => $document['status'],
        'source_url' => $document['source_url'],
        'content' => $document['content'],
        'version_label' => $document['version_label'],
    ]);
}

echo "Migration 010 completed successfully.";
