<?php

/**
 * Seed sample reports and report entries for the reporting module.
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

$reports = [
    [
        'title' => 'Weekly Executive Snapshot',
        'slug' => 'weekly-executive-snapshot-2026-03-03',
        'report_type' => 'executive',
        'department_slug' => 'executive',
        'owner_email' => 'admin@houseofsacci.com',
        'reporting_period_start' => '2026-03-01',
        'reporting_period_end' => '2026-03-07',
        'status' => 'published',
        'source_url' => null,
        'summary' => 'Leadership priorities are aligned around documenting operations, cleaning up compliance records, and consolidating internal knowledge in Brand Hub.',
        'entries' => [
            [
                'metric_key' => 'open_actions',
                'metric_label' => 'Open Action Items',
                'metric_value' => '2',
                'metric_unit' => 'items',
                'notes' => 'Initial follow-up work seeded from leadership and operations meetings.',
                'sort_order' => 1,
            ],
            [
                'metric_key' => 'active_docs',
                'metric_label' => 'Active Internal Documents',
                'metric_value' => '2',
                'metric_unit' => 'docs',
                'notes' => 'Foundational operating documents are now tracked in the system.',
                'sort_order' => 2,
            ],
        ],
    ],
    [
        'title' => 'Compliance Readiness Check',
        'slug' => 'compliance-readiness-check-2026-03-02',
        'report_type' => 'compliance',
        'department_slug' => 'compliance-metrc',
        'owner_email' => 'staff@houseofsacci.com',
        'reporting_period_start' => '2026-03-02',
        'reporting_period_end' => '2026-03-02',
        'status' => 'published',
        'source_url' => null,
        'summary' => 'Documentation consistency remains the main risk area. The team is actively standardizing file structure and accountability.',
        'entries' => [
            [
                'metric_key' => 'documentation_gap',
                'metric_label' => 'Documentation Gap',
                'metric_value' => 'Medium',
                'metric_unit' => null,
                'notes' => 'Main issue is inconsistent file organization across sensitive records.',
                'sort_order' => 1,
            ],
            [
                'metric_key' => 'priority_focus',
                'metric_label' => 'Priority Focus',
                'metric_value' => 'Folder Standardization',
                'metric_unit' => null,
                'notes' => 'Current action item is tied to the inventory and compliance review meeting.',
                'sort_order' => 2,
            ],
        ],
    ],
];

$reportStmt = $pdo->prepare(
    'INSERT INTO reports (
        title,
        slug,
        report_type,
        department_id,
        owner_user_id,
        reporting_period_start,
        reporting_period_end,
        status,
        source_url,
        summary
    ) VALUES (
        :title,
        :slug,
        :report_type,
        :department_id,
        :owner_user_id,
        :reporting_period_start,
        :reporting_period_end,
        :status,
        :source_url,
        :summary
    )
    ON DUPLICATE KEY UPDATE
        title = VALUES(title),
        report_type = VALUES(report_type),
        department_id = VALUES(department_id),
        owner_user_id = VALUES(owner_user_id),
        reporting_period_start = VALUES(reporting_period_start),
        reporting_period_end = VALUES(reporting_period_end),
        status = VALUES(status),
        source_url = VALUES(source_url),
        summary = VALUES(summary)'
);

$reportIdStmt = $pdo->prepare('SELECT id FROM reports WHERE slug = :slug LIMIT 1');
$entryExistsStmt = $pdo->prepare(
    'SELECT id FROM report_entries
     WHERE report_id = :report_id AND metric_key = :metric_key
     LIMIT 1'
);
$entryInsertStmt = $pdo->prepare(
    'INSERT INTO report_entries (
        report_id,
        metric_key,
        metric_label,
        metric_value,
        metric_unit,
        notes,
        sort_order
    ) VALUES (
        :report_id,
        :metric_key,
        :metric_label,
        :metric_value,
        :metric_unit,
        :notes,
        :sort_order
    )'
);

foreach ($reports as $report) {
    $reportStmt->execute([
        'title' => $report['title'],
        'slug' => $report['slug'],
        'report_type' => $report['report_type'],
        'department_id' => $departmentIds[$report['department_slug']] ?? null,
        'owner_user_id' => $userIds[$report['owner_email']] ?? null,
        'reporting_period_start' => $report['reporting_period_start'],
        'reporting_period_end' => $report['reporting_period_end'],
        'status' => $report['status'],
        'source_url' => $report['source_url'],
        'summary' => $report['summary'],
    ]);

    $reportIdStmt->execute(['slug' => $report['slug']]);
    $reportId = (int) $reportIdStmt->fetchColumn();

    if ($reportId <= 0) {
        continue;
    }

    foreach ($report['entries'] as $entry) {
        $entryExistsStmt->execute([
            'report_id' => $reportId,
            'metric_key' => $entry['metric_key'],
        ]);

        if ($entryExistsStmt->fetchColumn()) {
            continue;
        }

        $entryInsertStmt->execute([
            'report_id' => $reportId,
            'metric_key' => $entry['metric_key'],
            'metric_label' => $entry['metric_label'],
            'metric_value' => $entry['metric_value'],
            'metric_unit' => $entry['metric_unit'],
            'notes' => $entry['notes'],
            'sort_order' => $entry['sort_order'],
        ]);
    }
}

echo "Migration 012 completed successfully.";
