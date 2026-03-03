<?php

/**
 * Seed sample action items tied to the sample meetings.
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

$meetingIds = [];
$meetingStmt = $pdo->query('SELECT id, slug FROM meetings');
foreach ($meetingStmt->fetchAll() as $meeting) {
    $meetingIds[$meeting['slug']] = (int) $meeting['id'];
}

$items = [
    [
        'title' => 'Document weekly leadership priorities in Brand Hub',
        'details' => 'Create or update the week\'s leadership summary so department owners can align against the same priorities.',
        'status' => 'open',
        'priority' => 'high',
        'department_slug' => 'executive',
        'owner_email' => 'admin@houseofsacci.com',
        'created_by_email' => 'admin@houseofsacci.com',
        'source_type' => 'meeting',
        'source_slug' => 'weekly-leadership-sync-2026-03-03',
        'due_date' => '2026-03-05',
        'completed_at' => null,
    ],
    [
        'title' => 'Standardize compliance documentation folder structure',
        'details' => 'Consolidate inventory and compliance records into a consistent operating structure before additional reporting is built.',
        'status' => 'in_progress',
        'priority' => 'high',
        'department_slug' => 'compliance-metrc',
        'owner_email' => 'staff@houseofsacci.com',
        'created_by_email' => 'admin@houseofsacci.com',
        'source_type' => 'meeting',
        'source_slug' => 'inventory-compliance-review-2026-03-02',
        'due_date' => '2026-03-07',
        'completed_at' => null,
    ],
];

$existsStmt = $pdo->prepare(
    'SELECT id FROM action_items
     WHERE title = :title AND source_type = :source_type AND source_id <=> :source_id
     LIMIT 1'
);

$insertStmt = $pdo->prepare(
    'INSERT INTO action_items (
        title,
        details,
        status,
        priority,
        department_id,
        owner_user_id,
        created_by_user_id,
        source_type,
        source_id,
        due_date,
        completed_at
    ) VALUES (
        :title,
        :details,
        :status,
        :priority,
        :department_id,
        :owner_user_id,
        :created_by_user_id,
        :source_type,
        :source_id,
        :due_date,
        :completed_at
    )'
);

foreach ($items as $item) {
    $sourceId = null;
    if ($item['source_type'] === 'meeting') {
        $sourceId = $meetingIds[$item['source_slug']] ?? null;
    }

    $existsStmt->execute([
        'title' => $item['title'],
        'source_type' => $item['source_type'],
        'source_id' => $sourceId,
    ]);

    if ($existsStmt->fetchColumn()) {
        continue;
    }

    $insertStmt->execute([
        'title' => $item['title'],
        'details' => $item['details'],
        'status' => $item['status'],
        'priority' => $item['priority'],
        'department_id' => $departmentIds[$item['department_slug']] ?? null,
        'owner_user_id' => $userIds[$item['owner_email']] ?? null,
        'created_by_user_id' => $userIds[$item['created_by_email']] ?? null,
        'source_type' => $item['source_type'],
        'source_id' => $sourceId,
        'due_date' => $item['due_date'],
        'completed_at' => $item['completed_at'],
    ]);
}

echo "Migration 008 completed successfully.";
