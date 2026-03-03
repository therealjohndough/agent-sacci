<?php

/**
 * Seed sample meetings and decisions for the company-brain module.
 *
 * This gives the new meetings UI immediate content after the Phase 1
 * migrations run, while remaining safe to re-run during development.
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

$meetings = [
    [
        'title' => 'Weekly Leadership Sync',
        'slug' => 'weekly-leadership-sync-2026-03-03',
        'meeting_type' => 'leadership',
        'department_slug' => 'executive',
        'owner_email' => 'admin@houseofsacci.com',
        'scheduled_for' => '2026-03-03 09:00:00',
        'occurred_at' => '2026-03-03 09:00:00',
        'status' => 'published',
        'summary' => 'Reviewed priorities for marketing, compliance, and inventory operations for the week.',
        'notes' => "Reviewed current company priorities.\nConfirmed that meeting minutes and operational records will move into Sacci Brand Hub during Phase 1.\nAligned on the need for clearer ownership across departments.",
    ],
    [
        'title' => 'Inventory and Compliance Review',
        'slug' => 'inventory-compliance-review-2026-03-02',
        'meeting_type' => 'operations',
        'department_slug' => 'compliance-metrc',
        'owner_email' => 'staff@houseofsacci.com',
        'scheduled_for' => '2026-03-02 14:00:00',
        'occurred_at' => '2026-03-02 14:00:00',
        'status' => 'published',
        'summary' => 'Reviewed inventory accuracy, documentation gaps, and compliance handoff risks.',
        'notes' => "Reviewed current inventory tracking issues.\nFlagged the need for a more consistent documentation process for compliance-sensitive records.\nIdentified follow-up work for reporting and file organization.",
    ],
];

$meetingStmt = $pdo->prepare(
    'INSERT INTO meetings (
        title,
        slug,
        meeting_type,
        department_id,
        owner_user_id,
        scheduled_for,
        occurred_at,
        status,
        summary,
        notes
    ) VALUES (
        :title,
        :slug,
        :meeting_type,
        :department_id,
        :owner_user_id,
        :scheduled_for,
        :occurred_at,
        :status,
        :summary,
        :notes
    )
    ON DUPLICATE KEY UPDATE
        title = VALUES(title),
        meeting_type = VALUES(meeting_type),
        department_id = VALUES(department_id),
        owner_user_id = VALUES(owner_user_id),
        scheduled_for = VALUES(scheduled_for),
        occurred_at = VALUES(occurred_at),
        status = VALUES(status),
        summary = VALUES(summary),
        notes = VALUES(notes)'
);

foreach ($meetings as $meeting) {
    $meetingStmt->execute([
        'title' => $meeting['title'],
        'slug' => $meeting['slug'],
        'meeting_type' => $meeting['meeting_type'],
        'department_id' => $departmentIds[$meeting['department_slug']] ?? null,
        'owner_user_id' => $userIds[$meeting['owner_email']] ?? null,
        'scheduled_for' => $meeting['scheduled_for'],
        'occurred_at' => $meeting['occurred_at'],
        'status' => $meeting['status'],
        'summary' => $meeting['summary'],
        'notes' => $meeting['notes'],
    ]);
}

$meetingIdStmt = $pdo->prepare('SELECT id FROM meetings WHERE slug = :slug LIMIT 1');
$decisionExistsStmt = $pdo->prepare(
    'SELECT id FROM meeting_decisions WHERE meeting_id = :meeting_id AND decision = :decision LIMIT 1'
);
$decisionInsertStmt = $pdo->prepare(
    'INSERT INTO meeting_decisions (meeting_id, decision, owner_user_id, effective_date)
     VALUES (:meeting_id, :decision, :owner_user_id, :effective_date)'
);

$decisions = [
    [
        'meeting_slug' => 'weekly-leadership-sync-2026-03-03',
        'decision' => 'Use Sacci Brand Hub as the source of truth for meeting records and department knowledge during Phase 1.',
        'owner_email' => 'admin@houseofsacci.com',
        'effective_date' => '2026-03-03',
    ],
    [
        'meeting_slug' => 'weekly-leadership-sync-2026-03-03',
        'decision' => 'Standardize ownership by department before introducing any AI features.',
        'owner_email' => 'admin@houseofsacci.com',
        'effective_date' => '2026-03-03',
    ],
    [
        'meeting_slug' => 'inventory-compliance-review-2026-03-02',
        'decision' => 'Consolidate inventory and compliance reporting into structured internal records rather than scattered notes.',
        'owner_email' => 'staff@houseofsacci.com',
        'effective_date' => '2026-03-02',
    ],
];

foreach ($decisions as $decision) {
    $meetingIdStmt->execute(['slug' => $decision['meeting_slug']]);
    $meetingId = (int) $meetingIdStmt->fetchColumn();

    if ($meetingId <= 0) {
        continue;
    }

    $decisionExistsStmt->execute([
        'meeting_id' => $meetingId,
        'decision' => $decision['decision'],
    ]);

    if ($decisionExistsStmt->fetchColumn()) {
        continue;
    }

    $decisionInsertStmt->execute([
        'meeting_id' => $meetingId,
        'decision' => $decision['decision'],
        'owner_user_id' => $userIds[$decision['owner_email']] ?? null,
        'effective_date' => $decision['effective_date'],
    ]);
}

echo "Migration 006 completed successfully.";
