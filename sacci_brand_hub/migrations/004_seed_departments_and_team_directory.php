<?php

/**
 * Seed department records and the initial internal team directory.
 *
 * This migration avoids creating login accounts for staff when verified
 * emails and credentials are not yet established. Instead, it seeds the
 * department taxonomy and stores the current roster in settings so the app
 * has a structured source of truth to build from.
 */

use Core\Database;

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';

Database::init();
$pdo = Database::getConnection();

$departments = [
    [
        'name' => 'Executive',
        'slug' => 'executive',
        'description' => 'Leadership, approvals, and company-level priorities.',
    ],
    [
        'name' => 'Design and Marketing',
        'slug' => 'design-marketing',
        'description' => 'Brand design, campaign execution, and creative operations.',
    ],
    [
        'name' => 'Inventory',
        'slug' => 'inventory',
        'description' => 'Inventory oversight, system administration, and stock operations.',
    ],
    [
        'name' => 'Sales',
        'slug' => 'sales',
        'description' => 'Revenue operations, retailer relationships, and commercial reporting.',
    ],
    [
        'name' => 'Compliance / Metrc',
        'slug' => 'compliance-metrc',
        'description' => 'Metrc workflows, compliance records, and audit readiness.',
    ],
    [
        'name' => 'Cultivation',
        'slug' => 'cultivation',
        'description' => 'Grow operations, production notes, and product insight.',
    ],
    [
        'name' => 'Deliveries',
        'slug' => 'deliveries',
        'description' => 'Delivery logistics, routing, and fulfillment issue management.',
    ],
];

$departmentStmt = $pdo->prepare(
    'INSERT INTO departments (name, slug, description)
     VALUES (:name, :slug, :description)
     ON DUPLICATE KEY UPDATE
        name = VALUES(name),
        description = VALUES(description)'
);

foreach ($departments as $department) {
    $departmentStmt->execute($department);
}

$teamDirectory = [
    [
        'name' => 'Mike',
        'job_title' => 'CEO',
        'department_slug' => 'executive',
        'responsibilities' => [
            'Company priorities',
            'Executive approvals',
            'Major decision oversight',
        ],
    ],
    [
        'name' => 'John',
        'job_title' => 'Design and Marketing',
        'department_slug' => 'design-marketing',
        'responsibilities' => [
            'Design systems',
            'Marketing operations',
            'Creative direction',
        ],
    ],
    [
        'name' => 'Gilly',
        'job_title' => 'Inventory Manager and Site Admin',
        'department_slug' => 'inventory',
        'responsibilities' => [
            'Inventory oversight',
            'Operational system administration',
            'Site administration',
        ],
    ],
    [
        'name' => 'Max',
        'job_title' => 'Sales Director',
        'department_slug' => 'sales',
        'responsibilities' => [
            'Sales performance',
            'Retail relationships',
            'Commercial reporting',
        ],
    ],
    [
        'name' => 'Val',
        'job_title' => 'Metrc Manager',
        'department_slug' => 'compliance-metrc',
        'responsibilities' => [
            'Metrc workflows',
            'Compliance tracking',
            'Audit readiness',
        ],
    ],
    [
        'name' => 'Adam',
        'job_title' => 'Headgrower',
        'department_slug' => 'cultivation',
        'responsibilities' => [
            'Grow operations',
            'Production insight',
            'Strategy and creative brainstorming',
        ],
    ],
    [
        'name' => 'Jordan',
        'job_title' => 'Deliveries Manager',
        'department_slug' => 'deliveries',
        'responsibilities' => [
            'Delivery logistics',
            'Routing oversight',
            'Fulfillment issue resolution',
        ],
    ],
];

$settingsValue = json_encode($teamDirectory, JSON_UNESCAPED_SLASHES);

$settingsStmt = $pdo->prepare(
    'INSERT INTO settings (`key`, `value`)
     VALUES (:key_name, :value)
     ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)'
);

$settingsStmt->execute([
    'key_name' => 'team_directory_seed_v1',
    'value' => $settingsValue,
]);

echo "Migration 004 completed successfully.";
